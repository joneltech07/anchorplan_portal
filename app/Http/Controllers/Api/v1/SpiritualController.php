<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\DevotionalRecord;
use App\Models\MinistryInvolvement;
use App\Models\SundayServiceRecord;
use App\Models\User;
use App\Models\WednesdayPrayerRecord;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SpiritualController extends Controller
{
    private function scopedEmployees(Request $request)
    {
        $user = $request->user();

        // Department manager/team lead -> only their department (read-only enforced by update checks)
        if ($user->hasRole('department_manager') || $user->hasRole('team_lead')) {
            return User::query()
                ->where('is_active', true)
                ->where('department', $user->department)
                ->get(['id', 'department', 'cell_group_name', 'cell_group_role']);
        }

        // Admin/super admin/pastoral lead/hr -> all active employees
        return User::query()
            ->where('is_active', true)
            ->get(['id', 'department', 'cell_group_name', 'cell_group_role']);
    }

    private function ensureDevotionalRecordsForDate(Request $request, string $date): void
    {
        $users = $this->scopedEmployees($request);
        if ($users->isEmpty()) {
            return;
        }

        $existingUserIds = DevotionalRecord::where('date', $date)
            ->whereIn('user_id', $users->pluck('id'))
            ->pluck('user_id')
            ->all();

        $missing = $users->whereNotIn('id', $existingUserIds);

        $now = now();
        foreach ($missing as $u) {
            DevotionalRecord::create([
                'user_id' => $u->id,
                'date' => $date,
                'status' => 'none',
                'notes' => null,
                'monitored_by' => null,
                'monitored_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function ensurePrayerRecordsForDate(Request $request, string $date): void
    {
        $users = $this->scopedEmployees($request);
        if ($users->isEmpty()) {
            return;
        }

        $existingUserIds = WedednesdayPrayerRecord::where('wednesday_date', $date)
            ->whereIn('user_id', $users->pluck('id'))
            ->pluck('user_id')
            ->all();

        $missing = $users->whereNotIn('id', $existingUserIds);

        $now = now();
        foreach ($missing as $u) {
            WedednesdayPrayerRecord::create([
                'user_id' => $u->id,
                'wednesday_date' => $date,
                'attended' => false,
                'status' => 'absent',
                'absence_reason' => null,
                'monitored_by' => null,
                'monitored_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function ensureSundayRecordsForDate(Request $request, string $date): void
    {
        $users = $this->scopedEmployees($request);
        if ($users->isEmpty()) {
            return;
        }

        $existingUserIds = SundayServiceRecord::where('sunday_date', $date)
            ->whereIn('user_id', $users->pluck('id'))
            ->pluck('user_id')
            ->all();

        $missing = $users->whereNotIn('id', $existingUserIds);

        $now = now();
        foreach ($missing as $u) {
            SundayServiceRecord::create([
                'user_id' => $u->id,
                'sunday_date' => $date,
                'attended' => false,
                'status' => 'absent',
                'absence_reason' => null,
                'monitored_by' => null,
                'monitored_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function dashboard(Request $request)
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();
        $lastSunday = now()->subWeek()->startOfWeek()->addDays(6)->toDateString();


        $scopeQuery = User::query();
        if ($request->user()->hasRole('department_manager') || $request->user()->hasRole('team_lead')) {
            $scopeQuery->where('department', $request->user()->department);
        }

        $users = $scopeQuery->where('is_active', true)->get(['id', 'department', 'cell_group_name', 'cell_group_role']);

        $devSubmitted = DevotionalRecord::where('date', $today)->whereIn('user_id', $users->pluck('id'))->count();
        $devTotal = $users->count();
        $devPercent = $devTotal > 0 ? round(($devSubmitted / $devTotal) * 100) : 0;

        // Wednesday this week
        $wednesdayDates = [];
        $cursor = now()->startOfWeek();
        for ($i = 0; $i < 7; $i++) {
            $d = $cursor->copy()->addDays($i);
            if ($d->dayOfWeek === 3) { // Wednesday (Mon=1)
                $wednesdayDates[] = $d->toDateString();
            }
        }

        $wednesdayAttended = 0;
        $wednesdayTotal = 0;
        if (count($wednesdayDates) > 0) {
            $wednesdayTotal = $users->count();
$wednesdayAttended = WednesdayPrayerRecord::whereIn('wednesday_date', $wednesdayDates)
                ->whereIn('user_id', $users->pluck('id'))
                ->where('attended', true)
                ->distinct('user_id')
                ->count('user_id');
        }

        // Sunday last week (previous Sunday)
        $sundayTotal = $users->count();
        $sundayAttended = SundayServiceRecord::where('sunday_date', $lastSunday)
            ->whereIn('user_id', $users->pluck('id'))
            ->where('attended', true)
            ->distinct('user_id')
            ->count('user_id');

        // Top ministry types this week
        $ministryStats = MinistryInvolvement::whereBetween('eod_date', [$weekStart, $weekEnd])
            ->selectRaw('ministry_type, COUNT(*) as cnt')
            ->groupBy('ministry_type')
            ->orderByDesc('cnt')
            ->limit(1)
            ->get();
        $topMinistry = $ministryStats->first();

        return response()->json([
            'today' => $today,
            'summary' => [
                'devotional' => [
                    'submitted' => $devSubmitted,
                    'total' => $devTotal,
                    'percent' => $devPercent,
                ],
                'wednesday_prayer' => [
                    'attended' => $wednesdayAttended,
                    'total' => $wednesdayTotal,
                ],
                'sunday_service' => [
                    'attended' => $sundayAttended,
                    'total' => $sundayTotal,
                    'date' => $lastSunday,
                ],
            ],
            'top_ministry' => $topMinistry ? [
                'ministry_type' => $topMinistry->ministry_type,
                'count' => (int) $topMinistry->cnt,
            ] : null,
        ]);
    }

    public function devotionalRecords(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
            'department' => 'nullable|string',
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        $query = DevotionalRecord::with('user')
            ->where('date', $date);

        if ($request->user()->hasRole('department_manager') || $request->user()->hasRole('team_lead')) {
            $query->whereHas('user', fn ($q) => $q->where('department', $request->user()->department));
        }

        if (!empty($validated['department'])) {
            $query->whereHas('user', fn ($q) => $q->where('department', $validated['department']));
        }

        $records = $query->orderBy('user_id')->get();

        return response()->json($records);
    }

    public function updateDevotional(Request $request, string $userId)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:on_time,late,none',
            'notes' => 'nullable|string',
        ]);

        $record = DevotionalRecord::firstOrCreate([
            'user_id' => $userId,
            'date' => $data['date'],
        ], [
            'status' => 'none',
            'notes' => null,
        ]);

        $record->update([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'monitored_by' => $request->user()->id,
            'monitored_at' => now(),
        ]);

        return response()->json($record);
    }

    public function remindDevotional(Request $request, string $userId)
    {
        $user = User::findOrFail($userId);

        if (!$user->receive_devotional_reminders) {
            return response()->json(['message' => 'User opted out'], 200);
        }

        // Placeholder: dispatch notification
        return response()->json(['message' => 'Reminder queued (placeholder)']);
    }

    public function remindAll(Request $request)
    {
        // Placeholder: bulk reminders
        return response()->json(['message' => 'Bulk reminder queued (placeholder)']);
    }

    public function wednesdayRecords(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);
        $date = $validated['date'] ?? now()->toDateString();

        $records = WedednesdayPrayerRecord::with('user')
            ->where('wednesday_date', $date)
            ->get();

        return response()->json($records);
    }

    public function updateWednesday(Request $request, string $userId)
    {
        $data = $request->validate([
            'wednesday_date' => 'required|date',
            'attended' => 'required|boolean',
            'status' => 'required|in:attended,absent,excused',
            'absence_reason' => 'nullable|string',
        ]);

        $record = WedednesdayPrayerRecord::firstOrCreate([
            'user_id' => $userId,
            'wednesday_date' => $data['wednesday_date'],
        ]);

        $record->update([
            'attended' => $data['attended'],
            'status' => $data['status'],
            'absence_reason' => $data['absence_reason'] ?? null,
            'monitored_by' => $request->user()->id,
            'monitored_at' => now(),
        ]);

        return response()->json($record);
    }

    public function sundayRecords(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);
        $date = $validated['date'] ?? now()->toDateString();

        $records = SundayServiceRecord::with('user')
            ->where('sunday_date', $date)
            ->get();

        return response()->json($records);
    }

    public function updateSunday(Request $request, string $userId)
    {
        $data = $request->validate([
            'sunday_date' => 'required|date',
            'attended' => 'required|boolean',
            'status' => 'required|in:attended,absent,excused',
            'absence_reason' => 'nullable|string',
        ]);

        $record = SundayServiceRecord::firstOrCreate([
            'user_id' => $userId,
            'sunday_date' => $data['sunday_date'],
        ]);

        $record->update([
            'attended' => $data['attended'],
            'status' => $data['status'],
            'absence_reason' => $data['absence_reason'] ?? null,
            'monitored_by' => $request->user()->id,
            'monitored_at' => now(),
        ]);

        return response()->json($record);
    }

    public function ministryStats(Request $request)
    {
        $validated = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $validated['from'] ?? now()->startOfWeek()->toDateString();
        $to = $validated['to'] ?? now()->endOfWeek()->toDateString();

        $rows = MinistryInvolvement::whereBetween('eod_date', [$from, $to])
            ->selectRaw('ministry_type, COUNT(*) as cnt')
            ->groupBy('ministry_type')
            ->orderByDesc('cnt')
            ->get();

        return response()->json($rows);
    }

    public function ministryReports(Request $request)
    {
        $from = now()->subDays(7)->toDateString();
        $to = now()->toDateString();

        $reports = MinistryInvolvement::with('user')
            ->whereBetween('eod_date', [$from, $to])
            ->orderByDesc('eod_date')
            ->limit(50)
            ->get();

        return response()->json($reports);
    }

    public function storeEodMinistry(Request $request)
    {
        $data = $request->validate([
            'report_date' => 'required|date',
            'ministry_types' => 'nullable|array',
            'ministry_types.*' => 'string',
            'other_description' => 'nullable|string',
        ]);

        $selected = $data['ministry_types'] ?? [];

        if (in_array('none', $selected, true)) {
            MinistryInvolvement::where('user_id', $request->user()->id)
                ->where('eod_date', $data['report_date'])
                ->delete();

            return response()->json(['message' => 'No ministry selected']);
        }

        $otherDesc = $data['other_description'] ?? null;

        MinistryInvolvement::where('user_id', $request->user()->id)
            ->where('eod_date', $data['report_date'])
            ->delete();

        foreach ($selected as $type) {
            if ($type === 'other') {
                MinistryInvolvement::create([
                    'user_id' => $request->user()->id,
                    'eod_date' => $data['report_date'],
                    'ministry_type' => 'other',
                    'custom_description' => $otherDesc,
                ]);
                continue;
            }

            if ($type === 'none') {
                continue;
            }

            MinistryInvolvement::create([
                'user_id' => $request->user()->id,
                'eod_date' => $data['report_date'],
                'ministry_type' => $type,
            ]);
        }

        return response()->json(['message' => 'Saved']);
    }
}

