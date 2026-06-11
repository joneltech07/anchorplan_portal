<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'exception_date',
        'shift_type_id',
        'type', // 'custom_shift', 'day_off', 'holiday', 'half_day'
        'break_hours',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'exception_date' => 'date:Y-m-d',
            'break_hours' => 'decimal:1',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id');
    }
}
