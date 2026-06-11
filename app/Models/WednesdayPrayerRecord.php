<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Backwards compatibility alias (typo) for Wednesday prayer record model.
 * Main code should use WednesdayPrayerRecord.
 */
class WednesdayPrayerRecord extends Model
{
    use HasFactory;

    protected $table = 'wednesday_prayer_records';

    protected $fillable = [
        'user_id',
        'wednesday_date',
        'attended',
        'status',
        'absence_reason',
        'monitored_by',
        'monitored_at',
    ];

    protected $casts = [
        'wednesday_date' => 'date',
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

