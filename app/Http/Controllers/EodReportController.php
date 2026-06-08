<?php

namespace App\Http\Controllers;

use App\Models\EodReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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

        return Inertia::render('EOD/Dashboard', [
            'role' => $role,
            'reports' => $reports,
        ]);
    }

    public function create(Request $request)
    {
        // show form for today's EOD
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
        ]);

        $report = EodReport::create(array_merge($data, [
            'id' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]));

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
        ]);

        $eodReport->update(array_merge($data, [
            'submitted_at' => isset($data['status']) && $data['status'] === 'submitted' ? now() : $eodReport->submitted_at,
        ]));

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
        // managers: fetch team
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
}
