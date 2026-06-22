<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the attendance dashboard with timesheets.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        // Fetch today's clock status
        $todayRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Resolve date range (default to current month)
        $startDateStr = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDateStr = $request->input('end_date', Carbon::today()->endOfMonth()->format('Y-m-d'));

        // Fetch user's timesheets in range
        $myRecords = AttendanceRecord::with('user')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDateStr, $endDateStr])
            ->orderBy('date', 'desc')
            ->get();

        $timesheets = $this->transformRecords($myRecords);

        $teamTimesheets = [];
        $pendingOtApprovals = [];
        $isAdminOrManagerOrHr = $user->hasRole(['admin', 'manager', 'hr']);

        if ($isAdminOrManagerOrHr) {
            // Fetch all active employees' timesheets in range
            $teamRecords = AttendanceRecord::with('user')
                ->whereBetween('date', [$startDateStr, $endDateStr])
                ->orderBy('date', 'desc')
                ->get();

            $teamTimesheets = $this->transformRecords($teamRecords);

            // Fetch pending OT requests (excess hours worked, ot_status = 'pending')
            // To make sure we only grab completed days, clock_out_time must not be null
            $pendingRecords = AttendanceRecord::with('user')
                ->whereNotNull('clock_out_time')
                ->where('ot_status', 'pending')
                ->orderBy('date', 'desc')
                ->get();

            // Filter team records where actual hours > shift hours
            $pendingOtApprovals = $this->transformRecords($pendingRecords)->filter(function ($t) {
                return $t['status'] === 'Request for approval';
            })->values()->all();
        }

        return Inertia::render('Attendance/Index', [
            'todayRecord' => $todayRecord,
            'timesheets' => $timesheets,
            'teamTimesheets' => $teamTimesheets,
            'pendingOtApprovals' => $pendingOtApprovals,
            'isAdminOrHr' => $isAdminOrManagerOrHr,
            'isSuperAdmin' => $user->hasRole('super_admin'),
            'filters' => [
                'start_date' => $startDateStr,
                'end_date' => $endDateStr,
            ]
        ]);
    }

    private function transformRecords($records)
    {
        return $records->map(function ($record) {
            $shiftType = $record->getShiftType();
            $shiftHours = $record->getShiftHours();
            $actualHours = $record->getActualHours();
            $rate = $record->getHourlyRate();
            $payment = $record->getEstimatedPayment();
            $status = $record->getTimesheetStatus();

            $dateFormatted = Carbon::parse($record->date)->format('M-d-Y l');

            $actualDuration = $record->clock_out_time 
                ? $record->formatDurationSeconds(abs(Carbon::parse($record->clock_out_time)->diffInSeconds(Carbon::parse($record->clock_in_time))))
                : $record->formatDurationSeconds(abs(Carbon::now()->diffInSeconds(Carbon::parse($record->clock_in_time))));

            return [
                'id' => $record->id,
                'user_id' => $record->user_id,
                'user_name' => $record->user->name ?? '—',
                'user_department' => $record->user->department ?? '—',
                'user_position' => $record->user->position ?? '—',
                'date' => $dateFormatted,
                'raw_date' => $record->date ? $record->date->format('Y-m-d') : null,
                'shift_name' => $shiftType ? $shiftType->name : 'Standard Shift',
                'shift_hours' => ((float)$shiftHours == (int)$shiftHours ? (int)$shiftHours : number_format($shiftHours, 1)) . ' hrs',
                'raw_shift_hours' => $shiftHours,
                'actual_seconds' => $record->clock_out_time 
                    ? abs(Carbon::parse($record->clock_out_time)->diffInSeconds(Carbon::parse($record->clock_in_time)))
                    : abs(Carbon::now()->diffInSeconds(Carbon::parse($record->clock_in_time))),
                'actual_duration' => $actualDuration,
                'rate' => 'PHP ' . number_format($rate, 2),
                'raw_rate' => $rate,
                'estimated_payment' => 'PHP ' . number_format($payment, 2),
                'status' => $status,
                'ot_status' => $record->ot_status,
                'clock_in_time' => $record->clock_in_time ? $record->clock_in_time->toIso8601String() : null,
                'clock_out_time' => $record->clock_out_time ? $record->clock_out_time->toIso8601String() : null,
            ];
        });
    }

    /**
     * Submit clock-in.
     */
    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $exists = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'Already clocked in today.']);
        }

        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $now = Carbon::now();

        // Resolve today's shift type dynamically to compute the correct shift start time
        $tempRecord = new AttendanceRecord([
            'user_id' => $user->id,
            'date' => $today,
        ]);
        $shiftType = $tempRecord->getShiftType();

        $shiftStart = $shiftType 
            ? Carbon::today()->setTimeFromTimeString($shiftType->start_time)
            : Carbon::today()->setTime(9, 0, 0);

        $lateMinutes = 0;
        $status = 'present';

        if ($now->greaterThan($shiftStart)) {
            $lateMinutes = abs($now->diffInMinutes($shiftStart));
            $status = 'late';
        }

        DB::transaction(function () use ($user, $today, $now, $request, $status, $lateMinutes) {
            AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in_time' => $now,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $status,
                'late_minutes' => $lateMinutes,
            ]);
        });

        return back()->with('success', 'Clocked in successfully!');
    }

    /**
     * Submit clock-out.
     */
    public function clockOut(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $record = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $record) {
            return back()->withErrors(['message' => 'No active clock-in found for today.']);
        }

        if ($record->clock_out_time) {
            return back()->withErrors(['message' => 'Already clocked out today.']);
        }

        $record->update([
            'clock_out_time' => Carbon::now(),
        ]);

        return back()->with('success', 'Clocked out successfully!');
    }

    /**
     * Approve Overtime request.
     */
    public function approveOt(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasRole(['admin', 'manager', 'hr'])) {
            abort(403, 'Unauthorized');
        }

        $record = AttendanceRecord::findOrFail($id);
        $record->update([
            'ot_status' => 'approved',
            'ot_approved_by' => $user->id,
        ]);

        return back()->with('success', 'Overtime approved successfully.');
    }

    /**
     * Reject Overtime request.
     */
    public function rejectOt(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasRole(['admin', 'manager', 'hr'])) {
            abort(403, 'Unauthorized');
        }

        $record = AttendanceRecord::findOrFail($id);
        $record->update([
            'ot_status' => 'rejected',
            'ot_approved_by' => $user->id,
        ]);

        return back()->with('success', 'Overtime request rejected.');
    }

    /**
     * Edit attendance record (Super Admin only).
     */
    public function editAdmin(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasRole('super_admin')) {
            abort(403, 'Unauthorized. Super Admin only.');
        }

        $request->validate([
            'clock_in_time' => 'required|date',
            'clock_out_time' => 'nullable|date|after:clock_in_time',
            'password' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $record = AttendanceRecord::findOrFail($id);

        $clockIn = Carbon::parse($request->clock_in_time);
        $clockOut = $request->clock_out_time ? Carbon::parse($request->clock_out_time) : null;

        // Recalculate status and late minutes
        $shiftType = $record->getShiftType();
        $recordDateStr = $record->date ? $record->date->format('Y-m-d') : Carbon::parse($clockIn)->format('Y-m-d');
        
        $shiftStart = $shiftType 
            ? Carbon::parse($recordDateStr)->setTimeFromTimeString($shiftType->start_time)
            : Carbon::parse($recordDateStr)->setTime(9, 0, 0);

        $lateMinutes = 0;
        $status = 'present';

        if ($clockIn->greaterThan($shiftStart)) {
            $lateMinutes = abs($clockIn->diffInMinutes($shiftStart));
            $status = 'late';
        }

        $record->clock_in_time = $clockIn;
        $record->clock_out_time = $clockOut;
        $record->status = $status;
        $record->late_minutes = $lateMinutes;

        // Reset OT status if it doesn't exceed shift hours anymore
        if ($clockOut) {
            $actualHours = $record->getActualHours();
            $shiftHours = $record->getShiftHours();
            if ($actualHours <= $shiftHours) {
                $record->ot_status = 'pending';
                $record->ot_approved_by = null;
            }
        } else {
            $record->ot_status = 'pending';
            $record->ot_approved_by = null;
        }

        $record->save();

        return back()->with('success', 'Timesheet updated successfully.');
    }

    /**
     * Delete attendance record (Super Admin only).
     */
    public function deleteAdmin(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasRole('super_admin')) {
            abort(403, 'Unauthorized. Super Admin only.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $record = AttendanceRecord::findOrFail($id);
        $record->delete();

        return back()->with('success', 'Timesheet deleted successfully.');
    }
}
