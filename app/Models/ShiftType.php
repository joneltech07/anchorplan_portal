<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'break_hours',
        'hourly_rate',
        'night_differential_rate',
        'description',
        'is_active',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'break_hours' => 'decimal:1',
            'hourly_rate' => 'decimal:2',
            'night_differential_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class, 'shift_type_id');
    }

    public function scheduleExceptions()
    {
        return $this->hasMany(ScheduleException::class, 'shift_type_id');
    }
}
