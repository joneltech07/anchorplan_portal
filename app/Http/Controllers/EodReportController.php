<?php

namespace App\Http\Controllers;

use App\Exports\EodReportsExport;
use App\Models\EodReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\facades\Excel;


class EodReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $reports = EodReport::where('user_id', $user->id)
            ->with(['reviewer'])
            ->orderBy('report_date', 'desc')
            ->get();

        $role = 'employee';
        if ($user->hasRole('hr_manager')) {
            $role = 'hr';
        } elseif ($user->hasRole('general_manager')) {
            $role = 'gm';
        } elseif ($user->hasRole('department_manager') || $user->hasRole('team_lead')) {
            $role = 'manager';
        }

        $canViewTeamEod = $user->hasAnyRole(['super_admin', 'general_manager', 'hr_manager', 'department_manager', 'pastoral_lead', 'executive_assistant']);

        $departments = [];
        $employees = [];

        if ($canViewTeamEod) {
            $departments = \App\Models\User::whereNotNull('department')
                ->where('department', '!=', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department')
                ->toArray();

            $employees = \App\Models\User::orderBy('name')
                ->get(['id', 'name', 'department'])
                ->toArray();
        }

        return Inertia::render('EOD/Dashboard', [
            'role' => $role,
            'reports' => $reports,
            'canViewTeamEod' => $canViewTeamEod,
            'departments' => $departments,
            'employees' => $employees,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('EOD/Form', [
            'today' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'report_date' => 'required|date',
            'accomplishments' => 'required|string',
            'tomorrow_plan' => 'required|string',
            'blockers' => 'nullable|string',
            'hours_logged' => 'nullable|numeric',
            'task_ids_completed' => 'nullable|array',
            'mood_rating' => 'nullable|integer|min:1|max:5',
            'ministry_types' => 'nullable|array',
            'ministry_types.*' => 'string',
            'other_description' => 'nullable|string',
        ]);

        $report = EodReport::create(array_merge(\Illuminate\Support\Arr::except($data, ['ministry_types', 'other_description']), [
            'id' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]));

        $this->saveMinistryInvolvements(
            $request->user()->id,
            $data['report_date'],
            $data['ministry_types'] ?? [],
            $data['other_description'] ?? null
        );

        return redirect()->route('eod.show', $report->id);
    }

    public function show(EodReport $eodReport)
    {
        return Inertia::render('EOD/Show', [
            'report' => $eodReport->load('user', 'reviewer'),
        ]);
    }

    public function edit(EodReport $eodReport)
    {
        abort_unless($eodReport->status === 'draft' || $eodReport->user_id === Auth::id(), 403);
        $eodReport->load('ministryInvolvements');
        return Inertia::render('EOD/Form', ['report' => $eodReport]);
    }

    public function update(Request $request, EodReport $eodReport)
    {
        $data = $request->validate([
            'accomplishments' => 'required|string',
            'tomorrow_plan' => 'required|string',
            'blockers' => 'nullable|string',
            'hours_logged' => 'nullable|numeric',
            'task_ids_completed' => 'nullable|array',
            'mood_rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|in:draft,submitted',
            'ministry_types' => 'nullable|array',
            'ministry_types.*' => 'string',
            'other_description' => 'nullable|string',
        ]);

        $eodReport->update(array_merge(\Illuminate\Support\Arr::except($data, ['ministry_types', 'other_description']), [
            'submitted_at' => isset($data['status']) && $data['status'] === 'submitted' ? now() : $eodReport->submitted_at,
        ]));

        $this->saveMinistryInvolvements(
            $eodReport->user_id,
            $eodReport->report_date->toDateString(),
            $data['ministry_types'] ?? [],
            $data['other_description'] ?? null
        );

        return redirect()->route('eod.show', $eodReport->id);
    }

    public function review(Request $request, EodReport $eodReport)
    {
        $this->authorize('review', $eodReport);

        $data = $request->validate([
            'manager_feedback' => 'nullable|string',
            'status' => 'required|in:reviewed',
        ]);

        $eodReport->update([
            'manager_feedback' => $data['manager_feedback'] ?? null,
            'status' => 'reviewed',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back();
    }

    public function teamView(Request $request)
    {
        return Inertia::render('EOD/TeamView');
    }

    public function gmView(Request $request)
    {
        return Inertia::render('EOD/GMView');
    }

    public function hrView(Request $request)
    {
        return Inertia::render('EOD/HRView');
    }

    // ===== Employee EOD View & Excel Export =====

    public function employeeEodView(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user && $user->hasAnyRole(['super_admin', 'general_manager', 'hr_manager', 'department_manager', 'pastoral_lead', 'executive_assistant']),
            403
        );

        return Inertia::render('EOD/EmployeeEodView', [
            'filters' => $request->only(['date_from', 'date_to', 'department', 'employee_id', 'status']),
        ]);
    }

    public function getEmployeeEodData(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user && $user->hasAnyRole(['super_admin', 'general_manager', 'hr_manager', 'department_manager', 'pastoral_lead', 'executive_assistant']),
            403
        );

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->startOfMonth();

        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now();

        $query = EodReport::with(['user', 'ministryInvolvements'])
            ->whereBetween('report_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        if ($request->filled('department')) {
            $query->whereHas('user', fn ($q) => $q->where('department', $request->department));
        }

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user->hasRole('department_manager')) {
            $query->whereHas('user', fn ($q) => $q->where('department', $user->department));
        }

        $perPage = (int) $request->get('per_page', 50);
        $page = (int) $request->get('page', 1);

        $paginator = $query->orderBy('report_date', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->getCollection()->transform(function ($report) {
            $formattedMinistries = $report->ministryInvolvements?->map(function ($involvement) {
                if ($involvement->ministry_type === 'other') {
                    return $involvement->custom_description ?? 'Other';
                }
                $formatted = str_replace('_', ' ', $involvement->ministry_type);
                return ucwords($formatted);
            })->toArray() ?? [];

            return [
                'id' => $report->id,
                'employee_name' => $report->user?->name ?? '',
                'department' => $report->user?->department ?? '',
                'position' => $report->user?->position ?? '',
                'date' => optional($report->report_date)->format('Y-m-d'),
                'accomplishments' => Str::limit((string) ($report->accomplishments ?? ''), 100),
                'tomorrow_plan' => Str::limit((string) ($report->tomorrow_plan ?? ''), 100),
                'blockers' => (string) ($report->blockers ?? ''),
                'ministries' => $formattedMinistries,
                'status' => (string) ($report->status ?? ''),
                'submitted_at' => $report->submitted_at?->format('Y-m-d H:i'),
                'hours_logged' => $report->hours_logged,
                'mood_rating' => $report->mood_rating,
            ];
        });

        return response()->json([
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    public function exportEodReports(Request $request)
    {
        $user = $request->user();
        abort_unless(
            $user && $user->hasAnyRole(['super_admin', 'general_manager', 'hr_manager', 'department_manager', 'pastoral_lead', 'executive_assistant']),
            403
        );

        $dateFrom = $request->get('date_from')
            ? Carbon::parse($request->get('date_from'))->startOfDay()
            : now()->startOfMonth();

        $dateTo = $request->get('date_to')
            ? Carbon::parse($request->get('date_to'))->endOfDay()
            : now();

        $query = EodReport::with(['user', 'ministryInvolvements'])
            ->whereBetween('report_date', [$dateFrom->toDateString(), $dateTo->toDateString()]);

        if ($request->filled('department')) {
            $query->whereHas('user', fn ($q) => $q->where('department', $request->department));
        }

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user->hasRole('department_manager')) {
            $query->whereHas('user', fn ($q) => $q->where('department', $user->department));
        }

        $reports = $query->orderBy('report_date', 'desc')->get();

        $rows = $reports->map(function ($report) {
            $formattedMinistries = $report->ministryInvolvements?->map(function ($involvement) {
                if ($involvement->ministry_type === 'other') {
                    return $involvement->custom_description ?? 'Other';
                }
                $formatted = str_replace('_', ' ', $involvement->ministry_type);
                return ucwords($formatted);
            })->toArray() ?? [];

            return [
                'employee_name' => $report->user?->name ?? '',
                'department' => $report->user?->department ?? '',
                'position' => $report->user?->position ?? '',
                'date' => optional($report->report_date)->format('Y-m-d'),
                'accomplishments' => (string) ($report->accomplishments ?? ''),
                'tomorrow_plan' => (string) ($report->tomorrow_plan ?? ''),
                'blockers' => (string) ($report->blockers ?? ''),
                'ministries' => $formattedMinistries,
                'submitted_at' => $report->submitted_at?->format('Y-m-d H:i'),
            ];
        })->toArray();

        return Excel::download(new EodReportsExport($rows), 'eod-reports-' . now()->format('Y-m-d_His') . '.xlsx');
    }

    private function saveMinistryInvolvements($userId, $reportDate, array $ministryTypes, ?string $otherDescription)
    {
        // If the UI sent both 'none' and real ministries, keep only the real ones.
        if (in_array('none', $ministryTypes, true) && count(array_filter($ministryTypes, fn ($v) => $v !== 'none')) > 0) {
            $ministryTypes = array_values(array_filter($ministryTypes, fn ($v) => $v !== 'none'));
        }

        if (in_array('none', $ministryTypes, true) && count($ministryTypes) === 1) {
            \App\Models\MinistryInvolvement::where('user_id', $userId)
                ->where('eod_date', $reportDate)
                ->delete();
            return;
        }

        \App\Models\MinistryInvolvement::where('user_id', $userId)
            ->where('eod_date', $reportDate)
            ->delete();

        foreach ($ministryTypes as $type) {
            if ($type === 'other') {
                \App\Models\MinistryInvolvement::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $userId,
                    'eod_date' => $reportDate,
                    'ministry_type' => 'other',
                    'custom_description' => $otherDescription,
                ]);
                continue;
            }

            if ($type === 'none') {
                continue;
            }

            \App\Models\MinistryInvolvement::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $userId,
                'eod_date' => $reportDate,
                'ministry_type' => $type,
            ]);
        }
    }
}

