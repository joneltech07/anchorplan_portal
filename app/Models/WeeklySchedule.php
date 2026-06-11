<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monday' => 'array',
            'tuesday' => 'array',
            'wednesday' => 'array',
            'thursday' => 'array',
            'friday' => 'array',
            'saturday' => 'array',
            'sunday' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
