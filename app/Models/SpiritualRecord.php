<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Compatibility wrapper model for devotional records.
 * (Main schema uses devotional_records table for now.)
 */
class SpiritualRecord extends Model
{
    use HasFactory;

    protected $table = 'devotional_records';

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'notes',
        'monitored_by',
        'monitored_at',
    ];

    protected $casts = [
        'date' => 'date',
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

