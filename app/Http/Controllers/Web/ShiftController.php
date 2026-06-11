<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ShiftType;
use App\Models\EmployeeShift;
use App\Models\WeeklySchedule;
use App\Models\ScheduleException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Display the Shift Management & Scheduling dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Legacy role mapping support from User.php
        $isAdminOrHr = $user->hasRole(['super_admin', 'hr_manager', 'admin', 'hr']);

        $shiftTypes = ShiftType::orderBy('name')->get();
        $weeklySchedules = WeeklySchedule::orderBy('name')->get();

        $users = [];
        $regularAssignments = [];
        $exceptions = [];

        if ($isAdminOrHr) {
            $users = User::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'department']);

            $regularAssignments = EmployeeShift::with(['user:id,name,email', 'shiftType', 'assigner:id,name'])
                ->orderBy('start_date', 'desc')
                ->get();

            $exceptions = ScheduleException::with(['user:id,name,email', 'shiftType'])
                ->orderBy('exception_date', 'desc')
                ->get();
        } else {
            // Regular employee can only see their own assignments
            $regularAssignments = EmployeeShift::with(['shiftType', 'assigner:id,name'])
                ->where('user_id', $user->id)
                ->orderBy('start_date', 'desc')
                ->get();

            $exceptions = ScheduleException::with(['shiftType'])
                ->where('user_id', $user->id)
                ->orderBy('exception_date', 'desc')
                ->get();
        }

        return Inertia::render('Shifts/Index', [
            'shiftTypes' => $shiftTypes,
            'weeklySchedules' => $weeklySchedules,
            'users' => $users,
            'regularAssignments' => $regularAssignments,
            'exceptions' => $exceptions,
            'isAdminOrHr' => $isAdminOrHr,
        ]);
    }

    /**
     * Get shifts formatted as FullCalendar events.
     */
    public function getEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'user_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $isAdminOrHr = $user->hasRole(['super_admin', 'hr_manager', 'admin', 'hr']);

        // Determine target user(s)
        $targetUserId = $request->user_id;
        if (!$isAdminOrHr) {
            // Employees can only fetch their own events
            $targetUserId = $user->id;
        }

        $start = Carbon::parse($request->start)->startOfDay();
        $end = Carbon::parse($request->end)->endOfDay();

        $usersQuery = User::where('is_active', true);
        if ($targetUserId) {
            $usersQuery->where('id', $targetUserId);
        }
        $users = $usersQuery->get(['id', 'name', 'department']);

        // Fetch regular employee baseline assignments overlapping the range
        $employeeShifts = EmployeeShift::with('shiftType')
            ->where('status', 'active')
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end->format('Y-m-d'))
                  ->where(function ($sq) use ($start) {
                      $sq->whereNull('end_date')
                         ->orWhere('end_date', '>=', $start->format('Y-m-d'));
                  });
            })
            ->when($targetUserId, function ($q) use ($targetUserId) {
                return $q->where('user_id', $targetUserId);
            })
            ->get();

        // Fetch schedule exceptions in the range
        $exceptions = ScheduleException::with('shiftType')
            ->whereBetween('exception_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->when($targetUserId, function ($q) use ($targetUserId) {
                return $q->where('user_id', $targetUserId);
            })
            ->get()
            ->groupBy('user_id');

        $events = [];

        foreach ($users as $userItem) {
            $userExceptions = $exceptions->get($userItem->id, collect());
            $userRegularShifts = $employeeShifts->where('user_id', $userItem->id);

            $current = $start->copy();
            while ($current->lte($end)) {
                $dateStr = $current->format('Y-m-d');
                $exception = $userExceptions->firstWhere('exception_date', $dateStr);

                if ($exception) {
                    // Overridden by exception
                    if (in_array($exception->type, ['day_off', 'holiday'])) {
                        // Render day off or holiday event
                        $events[] = [
                            'id' => 'exception_' . $exception->id,
                            'title' => ($targetUserId ? '' : $userItem->name . ': ') . ucfirst(str_replace('_', ' ', $exception->type)) . ($exception->reason ? " - " . $exception->reason : ""),
                            'start' => $dateStr,
                            'allDay' => true,
                            'backgroundColor' => '#EF4444', // Red color
                            'borderColor' => '#EF4444',
                            'textColor' => '#ffffff',
                            'extendedProps' => [
                                'type' => 'exception',
                                'exception_type' => $exception->type,
                                'reason' => $exception->reason,
                                'exception_id' => $exception->id,
                                'employee_name' => $userItem->name,
                                'date' => $dateStr,
                            ]
                        ];
                    } else {
                        // Custom shift or Half day
                        $shiftType = $exception->shiftType;
                        if ($shiftType) {
                            $startDateTime = $dateStr . 'T' . $shiftType->start_time;
                            $endDateTime = $dateStr . 'T' . $shiftType->end_time;
                            if ($shiftType->end_time < $shiftType->start_time) {
                                $endDateTime = Carbon::parse($dateStr)->addDay()->format('Y-m-d') . 'T' . $shiftType->end_time;
                            }

                            $events[] = [
                                'id' => 'exception_' . $exception->id,
                                'title' => ($targetUserId ? '' : $userItem->name . ': ') . $shiftType->name . ' (' . ucfirst(str_replace('_', ' ', $exception->type)) . ')',
                                'start' => $startDateTime,
                                'end' => $endDateTime,
                                'backgroundColor' => '#F59E0B', // Amber color for custom overrides
                                'borderColor' => '#F59E0B',
                                'textColor' => '#ffffff',
                                'extendedProps' => [
                                    'type' => 'exception',
                                    'exception_type' => $exception->type,
                                    'shift_type_id' => $shiftType->id,
                                    'shift_name' => $shiftType->name,
                                    'hours' => substr($shiftType->start_time, 0, 5) . ' - ' . substr($shiftType->end_time, 0, 5),
                                    'break_hours' => $exception->break_hours ?? $shiftType->break_hours,
                                    'hourly_rate' => $shiftType->hourly_rate,
                                    'night_differential_rate' => $shiftType->night_differential_rate,
                                    'reason' => $exception->reason,
                                    'exception_id' => $exception->id,
                                    'employee_name' => $userItem->name,
                                    'date' => $dateStr,
                                ]
                            ];
                        }
                    }
                } else {
                    // Check if regular baseline shift assignment covers this date
                    // Work days are Monday to Friday (Friday shift ending Saturday morning is covered naturally)
                    $dayOfWeek = $current->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                    $isWorkDay = $dayOfWeek >= 1 && $dayOfWeek <= 5; // Monday to Friday

                    if ($isWorkDay) {
                        $activeShift = $userRegularShifts->first(function ($shift) use ($current) {
                            return $current->gte($shift->start_date) && (is_null($shift->end_date) || $current->lte($shift->end_date));
                        });

                        if ($activeShift) {
                            $shiftType = $activeShift->shiftType;
                            $startDateTime = $dateStr . 'T' . $shiftType->start_time;
                            $endDateTime = $dateStr . 'T' . $shiftType->end_time;
                            if ($shiftType->end_time < $shiftType->start_time) {
                                $endDateTime = Carbon::parse($dateStr)->addDay()->format('Y-m-d') . 'T' . $shiftType->end_time;
                            }

                            $events[] = [
                                'id' => 'regular_' . $activeShift->id . '_' . $dateStr,
                                'title' => ($targetUserId ? '' : $userItem->name . ': ') . $shiftType->name,
                                'start' => $startDateTime,
                                'end' => $endDateTime,
                                'backgroundColor' => $shiftType->color ?? '#3B82F6',
                                'borderColor' => $shiftType->color ?? '#3B82F6',
                                'textColor' => '#ffffff',
                                'extendedProps' => [
                                    'type' => 'regular',
                                    'shift_assignment_id' => $activeShift->id,
                                    'shift_type_id' => $shiftType->id,
                                    'shift_name' => $shiftType->name,
                                    'hours' => substr($shiftType->start_time, 0, 5) . ' - ' . substr($shiftType->end_time, 0, 5),
                                    'break_hours' => $shiftType->break_hours,
                                    'hourly_rate' => $shiftType->hourly_rate,
                                    'night_differential_rate' => $shiftType->night_differential_rate,
                                    'notes' => $activeShift->notes,
                                    'employee_name' => $userItem->name,
                                    'date' => $dateStr,
                                ]
                            ];
                        }
                    }
                }

                $current->addDay();
            }
        }

        return response()->json($events);
    }

    /**
     * Create a shift type.
     */
    public function storeShiftType(Request $request)
    {
        $this->authorizeAdminOrHr();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shift_types,code',
            'start_time' => 'required|string', // HH:MM or HH:MM:SS
            'end_time' => 'required|string',
            'break_hours' => 'required|numeric|in:0,0.5,1,1.5,2',
            'hourly_rate' => 'required|numeric|min:0',
            'night_differential_rate' => 'nullable|numeric|min:1',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7', // Hex color code
            'is_active' => 'boolean',
        ]);

        ShiftType::create($validated);

        return back()->with('success', 'Shift type created successfully.');
    }

    /**
     * Update a shift type.
     */
    public function updateShiftType(Request $request, ShiftType $shiftType)
    {
        $this->authorizeAdminOrHr();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:shift_types,code,' . $shiftType->id,
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'break_hours' => 'required|numeric|in:0,0.5,1,1.5,2',
            'hourly_rate' => 'required|numeric|min:0',
            'night_differential_rate' => 'nullable|numeric|min:1',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $shiftType->update($validated);

        return back()->with('success', 'Shift type updated successfully.');
    }

    /**
     * Delete a shift type.
     */
    public function destroyShiftType(ShiftType $shiftType)
    {
        $this->authorizeAdminOrHr();
        $shiftType->delete();

        return back()->with('success', 'Shift type deleted successfully.');
    }

    /**
     * Assign regular shift or add a date override.
     */
    public function assignShift(Request $request)
    {
        $this->authorizeAdminOrHr();

        $request->validate([
            'assignment_type' => 'required|in:regular,exception,weekly_template',
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user_id;
        $type = $request->assignment_type;

        if ($type === 'regular') {
            $validated = $request->validate([
                'shift_type_id' => 'required|exists:shift_types,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'notes' => 'nullable|string',
            ]);

            EmployeeShift::create([
                'user_id' => $userId,
                'shift_type_id' => $validated['shift_type_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'active',
                'assigned_by' => $request->user()->id,
            ]);

            return back()->with('success', 'Baseline shift assigned successfully.');
        } 
        
        if ($type === 'exception') {
            $validated = $request->validate([
                'exception_date' => 'required|date',
                'type' => 'required|in:custom_shift,day_off,holiday,half_day',
                'shift_type_id' => 'required_if:type,custom_shift,half_day|nullable|exists:shift_types,id',
                'break_hours' => 'nullable|numeric|in:0,0.5,1,1.5,2',
                'reason' => 'nullable|string',
            ]);

            ScheduleException::updateOrCreate(
                [
                    'user_id' => $userId,
                    'exception_date' => $validated['exception_date'],
                ],
                [
                    'shift_type_id' => in_array($validated['type'], ['day_off', 'holiday']) ? null : $validated['shift_type_id'],
                    'type' => $validated['type'],
                    'break_hours' => in_array($validated['type'], ['day_off', 'holiday']) ? null : ($validated['break_hours'] ?? null),
                    'reason' => $validated['reason'] ?? null,
                ]
            );

            return back()->with('success', 'Schedule exception saved successfully.');
        }

        if ($type === 'weekly_template') {
            $validated = $request->validate([
                'weekly_schedule_id' => 'required|exists:weekly_schedules,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $template = WeeklySchedule::findOrFail($validated['weekly_schedule_id']);
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            DB::transaction(function () use ($userId, $template, $startDate, $endDate) {
                $current = $startDate->copy();
                while ($current->lte($endDate)) {
                    $dayName = strtolower($current->englishDayOfWeek); // e.g., 'monday'
                    
                    // Retrieve template config for this day
                    $dayConfig = $template->{$dayName};

                    if ($dayConfig) {
                        $shiftTypeId = $dayConfig['shift_type_id'] ?? null;
                        $breakHours = $dayConfig['break_hours'] ?? null;

                        if ($shiftTypeId) {
                            ScheduleException::updateOrCreate(
                                [
                                    'user_id' => $userId,
                                    'exception_date' => $current->format('Y-m-d'),
                                ],
                                [
                                    'shift_type_id' => $shiftTypeId,
                                    'type' => 'custom_shift',
                                    'break_hours' => $breakHours,
                                    'reason' => 'Applied template: ' . $template->name,
                                ]
                            );
                        } else {
                            ScheduleException::updateOrCreate(
                                [
                                    'user_id' => $userId,
                                    'exception_date' => $current->format('Y-m-d'),
                                ],
                                [
                                    'shift_type_id' => null,
                                    'type' => 'day_off',
                                    'break_hours' => null,
                                    'reason' => 'Day off from template: ' . $template->name,
                                ]
                            );
                        }
                    }
                    $current->addDay();
                }
            });

            return back()->with('success', 'Weekly template applied successfully over date range.');
        }

        return back()->withErrors(['message' => 'Invalid assignment type.']);
    }

    /**
     * Delete/terminate baseline shift assignment.
     */
    public function deleteAssignment($id)
    {
        $this->authorizeAdminOrHr();
        
        $assignment = EmployeeShift::findOrFail($id);
        $assignment->delete();

        return back()->with('success', 'Baseline shift assignment removed.');
    }

    /**
     * Create or update weekly schedule templates.
     */
    public function storeWeeklySchedule(Request $request)
    {
        $this->authorizeAdminOrHr();

        $validated = $request->validate([
            'id' => 'nullable|uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monday' => 'nullable|array',
            'tuesday' => 'nullable|array',
            'wednesday' => 'nullable|array',
            'thursday' => 'nullable|array',
            'friday' => 'nullable|array',
            'saturday' => 'nullable|array',
            'sunday' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        WeeklySchedule::updateOrCreate(
            ['id' => $validated['id'] ?? (string) \Illuminate\Support\Str::uuid()],
            $validated
        );

        return back()->with('success', 'Weekly schedule template saved.');
    }

    /**
     * Delete weekly schedule template.
     */
    public function destroyWeeklySchedule(WeeklySchedule $weeklySchedule)
    {
        $this->authorizeAdminOrHr();
        $weeklySchedule->delete();

        return back()->with('success', 'Weekly schedule template deleted.');
    }

    /**
     * Add manual exception override.
     */
    public function storeException(Request $request)
    {
        $this->authorizeAdminOrHr();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'exception_date' => 'required|date',
            'type' => 'required|in:custom_shift,day_off,holiday,half_day',
            'shift_type_id' => 'required_if:type,custom_shift,half_day|nullable|exists:shift_types,id',
            'break_hours' => 'nullable|numeric|in:0,0.5,1,1.5,2',
            'reason' => 'nullable|string',
        ]);

        ScheduleException::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'exception_date' => $validated['exception_date'],
            ],
            [
                'shift_type_id' => in_array($validated['type'], ['day_off', 'holiday']) ? null : $validated['shift_type_id'],
                'type' => $validated['type'],
                'break_hours' => in_array($validated['type'], ['day_off', 'holiday']) ? null : ($validated['break_hours'] ?? null),
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return back()->with('success', 'Exception override saved.');
    }

    /**
     * Delete exception override.
     */
    public function deleteException($id)
    {
        $this->authorizeAdminOrHr();
        
        $exception = ScheduleException::findOrFail($id);
        $exception->delete();

        return back()->with('success', 'Exception override deleted.');
    }

    /**
     * Helper to verify permission.
     */
    protected function authorizeAdminOrHr()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole(['super_admin', 'hr_manager', 'admin', 'hr'])) {
            abort(403, 'Unauthorized action.');
        }
    }
}
