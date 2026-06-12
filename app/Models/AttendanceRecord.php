<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'latitude',
        'longitude',
        'status', // 'present', 'late', 'absent', 'half_day'
        'late_minutes',
        'ot_status',
        'ot_approved_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'clock_in_time' => 'datetime',
            'clock_out_time' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'late_minutes' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getShiftType()
    {
        // 1. Check schedule exception for this date
        $exception = ScheduleException::where('user_id', $this->user_id)
            ->where('exception_date', $this->date ? $this->date->format('Y-m-d') : null)
            ->first();

        if ($exception) {
            if (in_array($exception->type, ['day_off', 'holiday'])) {
                return null;
            }
            return $exception->shiftType; // custom_shift or half_day
        }

        // 2. Check regular employee shift assignment
        if ($this->date) {
            $dayOfWeek = $this->date->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
            if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $activeShift = EmployeeShift::where('user_id', $this->user_id)
                    ->where('status', 'active')
                    ->where('start_date', '<=', $this->date->format('Y-m-d'))
                    ->where(function ($query) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $this->date->format('Y-m-d'));
                    })
                    ->first();

                if ($activeShift) {
                    return $activeShift->shiftType;
                }
            }
        }

        return null;
    }

    public function getShiftHours()
    {
        $shiftType = $this->getShiftType();
        if (!$shiftType) {
            return 8.0; // fallback standard shift is 8 hours
        }

        $start = \Carbon\Carbon::parse($shiftType->start_time);
        $end = \Carbon\Carbon::parse($shiftType->end_time);
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $hours = abs($end->diffInSeconds($start)) / 3600.0;
        return max(0, $hours - (float)$shiftType->break_hours);
    }

    public function getBreakHours()
    {
        // 1. Check schedule exception for this date
        $exception = ScheduleException::where('user_id', $this->user_id)
            ->where('exception_date', $this->date ? $this->date->format('Y-m-d') : null)
            ->first();

        if ($exception) {
            if (in_array($exception->type, ['day_off', 'holiday'])) {
                return 0.0;
            }
            if ($exception->break_hours !== null) {
                return (float) $exception->break_hours;
            }
            $shiftType = $exception->shiftType;
            if ($shiftType) {
                return (float) $shiftType->break_hours;
            }
        }

        // 2. Check regular shift assignment
        $shiftType = $this->getShiftType();
        if ($shiftType) {
            return (float) $shiftType->break_hours;
        }

        return 1.0; // fallback standard break hours is 1.0
    }

    public function calculateNightHoursInRange($in, $out)
    {
        $timezone = 'Asia/Manila';
        $inLocal = $in->copy()->setTimezone($timezone);
        $outLocal = $out->copy()->setTimezone($timezone);

        if ($outLocal->lessThan($inLocal)) {
            return 0.0;
        }

        $startDate = $inLocal->copy()->startOfDay();
        $endDate = $outLocal->copy()->startOfDay();

        $totalNightSeconds = 0;

        for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
            // Window 1: 10:00 PM (22:00) to 6:00 AM (06:00) next day local time
            $windowStart = $date->copy()->setTime(22, 0, 0);
            $windowEnd = $date->copy()->addDay()->setTime(6, 0, 0);

            $overlapStart = $inLocal->greaterThan($windowStart) ? $inLocal : $windowStart;
            $overlapEnd = $outLocal->lessThan($windowEnd) ? $outLocal : $windowEnd;

            if ($overlapStart->lessThan($overlapEnd)) {
                $totalNightSeconds += abs($overlapEnd->diffInSeconds($overlapStart));
            }
        }

        // Also check the day before in case the shift started before 6 AM local time
        $prevDate = $startDate->copy()->subDay();
        $windowStart = $prevDate->copy()->setTime(22, 0, 0);
        $windowEnd = $prevDate->copy()->addDay()->setTime(6, 0, 0);

        $overlapStart = $inLocal->greaterThan($windowStart) ? $inLocal : $windowStart;
        $overlapEnd = $outLocal->lessThan($windowEnd) ? $outLocal : $windowEnd;

        if ($overlapStart->lessThan($overlapEnd)) {
            $totalNightSeconds += abs($overlapEnd->diffInSeconds($overlapStart));
        }

        return round($totalNightSeconds / 3600.0, 4);
    }

    public function getNightDifferentialHours()
    {
        if (!$this->clock_in_time) {
            return 0.0;
        }

        $in = \Carbon\Carbon::parse($this->clock_in_time);
        $out = $this->clock_out_time ? \Carbon\Carbon::parse($this->clock_out_time) : \Carbon\Carbon::now();

        return $this->calculateNightHoursInRange($in, $out);
    }

    public function getActualHours()
    {
        if (!$this->clock_in_time) {
            return 0.0;
        }
        $in = \Carbon\Carbon::parse($this->clock_in_time);
        $out = $this->clock_out_time ? \Carbon\Carbon::parse($this->clock_out_time) : \Carbon\Carbon::now();
        $totalHours = abs($out->diffInSeconds($in)) / 3600.0;
        $breakHours = $this->getBreakHours();
        return round(max(0.0, $totalHours - $breakHours), 4);
    }

    public function getHourlyRate()
    {
        $shiftType = $this->getShiftType();
        if ($shiftType && $shiftType->hourly_rate > 0) {
            return (float) $shiftType->hourly_rate;
        }

        // Fallback to user hourly rate
        $user = $this->user ?: User::find($this->user_id);
        if ($user && $user->hourly_rate > 0) {
            return (float) $user->hourly_rate;
        }

        return 0.0;
    }

    public function getEstimatedPayment()
    {
        if (!$this->clock_out_time) {
            return 0.0;
        }

        $rate = $this->getHourlyRate();
        $shiftHours = $this->getShiftHours();

        $in = \Carbon\Carbon::parse($this->clock_in_time);
        $out = \Carbon\Carbon::parse($this->clock_out_time);
        $totalHours = abs($out->diffInSeconds($in)) / 3600.0;
        $breakHours = $this->getBreakHours();

        $shiftType = $this->getShiftType();
        $multiplier = ($shiftType && $shiftType->night_differential_rate > 0)
            ? (float) $shiftType->night_differential_rate
            : 1.10;
        $nightRate = $rate * $multiplier;

        $actualHours = max(0.0, $totalHours - $breakHours);
        $isOvertime = $actualHours > $shiftHours;

        if ($isOvertime) {
            $regularElapsedHours = $shiftHours + $breakHours;
            $regularEnd = $in->copy()->addSeconds((int)round($regularElapsedHours * 3600));
            if ($out->lessThan($regularEnd)) {
                $regularEnd = $out;
            }
        } else {
            $regularEnd = $out;
        }

        $regularNightHours = $this->calculateNightHoursInRange($in, $regularEnd);
        $regularElapsed = abs($regularEnd->diffInSeconds($in)) / 3600.0;
        $regularStandardHours = max(0.0, $regularElapsed - $regularNightHours);

        $paidNightHours = max(0.0, $regularNightHours - $breakHours);
        $remainingBreak = max(0.0, $breakHours - $regularNightHours);
        $paidStandardHours = max(0.0, $regularStandardHours - $remainingBreak);

        $basePayment = ($paidStandardHours * $rate) + ($paidNightHours * $nightRate);

        $otPayment = 0.0;
        if ($isOvertime) {
            $otHours = $actualHours - $shiftHours;
            $otPayment = $this->ot_status === 'approved' ? ($otHours * $rate * 1.5) : 0.0;
        }

        $payment = $basePayment + $otPayment;

        return round(max(0.0, $payment), 2);
    }

    public function getTimesheetStatus()
    {
        if (!$this->clock_out_time) {
            return 'Ongoing';
        }

        $actualHours = $this->getActualHours();
        $shiftHours = $this->getShiftHours();

        if ($actualHours > $shiftHours) {
            if ($this->ot_status === 'approved') {
                return 'Approved';
            } elseif ($this->ot_status === 'rejected') {
                return 'Approved'; // Exceeded shift but rejected OT means the shift is approved, but OT is not paid.
            } else {
                return 'Request for approval';
            }
        }

        return 'Approved';
    }

    public function formatDurationSeconds($seconds)
    {
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        
        $parts = [];
        if ($h > 0) $parts[] = "{$h} hrs";
        if ($m > 0) $parts[] = "{$m} mins";
        if ($s > 0 || empty($parts)) $parts[] = "{$s} secs";
        
        return implode(' ', $parts);
    }

}
