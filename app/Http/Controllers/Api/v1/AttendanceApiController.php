<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class AttendanceApiController extends Controller
{
    /**
     * Get today's attendance status for authenticated user.
     */
    public function today(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $record = AttendanceRecord::where('user_id', $request->user()->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'record' => $record ? new AttendanceRecordResource($record) : null,
            'clocked_in' => $record !== null,
            'clocked_out' => $record && $record->clock_out_time !== null,
        ]);
    }

    /**
     * Get 7-day attendance summary.
     */
    public function summary(Request $request)
    {
        $startDate = Carbon::today()->subDays(6)->format('Y-m-d');
        $endDate = Carbon::today()->format('Y-m-d');

        $records = AttendanceRecord::where('user_id', $request->user()->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        return AttendanceRecordResource::collection($records);
    }

    /**
     * Clock in.
     */
    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        // Check if already clocked in
        $exists = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already clocked in today.'], 422);
        }

        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $now = Carbon::now();
        $shiftStart = Carbon::today()->setTime(9, 0, 0); // Standard shift start at 09:00

        $lateMinutes = 0;
        $status = 'present';

        if ($now->greaterThan($shiftStart)) {
            $lateMinutes = $now->diffInMinutes($shiftStart);
            $status = 'late';
        }

        $record = DB::transaction(function () use ($user, $today, $now, $request, $status, $lateMinutes) {
            return AttendanceRecord::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in_time' => $now,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => $status,
                'late_minutes' => $lateMinutes,
            ]);
        });

        return response()->json([
            'message' => 'Clocked in successfully!',
            'record' => new AttendanceRecordResource($record),
        ]);
    }

    /**
     * Clock out.
     */
    public function clockOut(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $record = AttendanceRecord::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $record) {
            return response()->json(['message' => 'You must clock in first before clocking out.'], 422);
        }

        if ($record->clock_out_time) {
            return response()->json(['message' => 'You have already clocked out today.'], 422);
        }

        $now = Carbon::now();
        $record->update([
            'clock_out_time' => $now,
        ]);

        return response()->json([
            'message' => 'Clocked out successfully!',
            'record' => new AttendanceRecordResource($record),
        ]);
    }

    /**
     * Get team attendance calendar (Managers, HR, Admin).
     */
    public function team(Request $request)
    {
        // Authorize with policy viewAny
        if ($request->user()->cannot('viewAny', AttendanceRecord::class)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $records = AttendanceRecord::with('user')
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get();

        return AttendanceRecordResource::collection($records);
    }
}
