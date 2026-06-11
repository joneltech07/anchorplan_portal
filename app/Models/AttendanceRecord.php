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

        $hours = $end->diffInSeconds($start) / 3600.0;
        return max(0, $hours - (float)$shiftType->break_hours);
    }

    public function getActualHours()
    {
        if (!$this->clock_in_time) {
            return 0.0;
        }
        $in = \Carbon\Carbon::parse($this->clock_in_time);
        $out = $this->clock_out_time ? \Carbon\Carbon::parse($this->clock_out_time) : \Carbon\Carbon::now();
        return round($out->diffInSeconds($in) / 3600.0, 4);
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
        $rate = $this->getHourlyRate();
        $shiftHours = $this->getShiftHours();
        
        if (!$this->clock_out_time) {
            return 0.0; // Ongoing shifts estimated payment is 0.00
        }

        $actualHours = $this->getActualHours();

        if ($actualHours <= $shiftHours) {
            return round($actualHours * $rate, 2);
        }

        // Exceeded shift hours
        if ($this->ot_status === 'approved') {
            return round(($shiftHours * $rate) + (($actualHours - $shiftHours) * $rate * 1.5), 2);
        } else {
            return round($shiftHours * $rate, 2);
        }
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
