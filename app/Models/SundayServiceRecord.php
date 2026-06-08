<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SundayServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sunday_date',
        'attended',
        'status',
        'absence_reason',
        'monitored_by',
        'monitored_at',
    ];

    protected $casts = [
        'sunday_date' => 'date',
        'attended' => 'boolean',
        'monitored_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function monitoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monitored_by');
    }
}

