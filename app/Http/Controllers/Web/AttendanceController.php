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
     * Display the attendance dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        // Fetch today's clock status
        $todayRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // 7 days summary history
        $sevenDaysAgo = Carbon::today()->subDays(6)->format('Y-m-d');
        $history = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('date', [$sevenDaysAgo, $today])
            ->orderBy('date', 'desc')
            ->get();

        $teamAttendance = [];
        $teamEmployees = [];

        // If manager/admin/hr, fetch team data
        if ($user->hasRole(['admin', 'manager', 'hr'])) {
            $teamEmployees = User::where('is_active', true)
                ->select('id', 'name', 'email', 'role', 'department')
                ->get();

            // Fetch team attendance for the current month
            $startOfMonth = Carbon::today()->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::today()->endOfMonth()->format('Y-m-d');
            
            $teamAttendance = AttendanceRecord::with('user:id,name,email')
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get();
        }

        return Inertia::render('Attendance/Index', [
            'todayRecord' => $todayRecord,
            'history' => $history,
            'teamEmployees' => $teamEmployees,
            'teamAttendance' => $teamAttendance,
        ]);
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
        $shiftStart = Carbon::today()->setTime(9, 0, 0);

        $lateMinutes = 0;
        $status = 'present';

        if ($now->greaterThan($shiftStart)) {
            $lateMinutes = $now->diffInMinutes($shiftStart);
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
}
