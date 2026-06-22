<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EodReport extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'report_date',
        'accomplishments',
        'tomorrow_plan',
        'blockers',
        'hours_logged',
        'task_ids_completed',
        'mood_rating',
        'status',
        'submitted_at',
        'manager_feedback',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'task_ids_completed' => 'array',
        'hours_logged' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function ministryInvolvements()
    {
        // EOD report belongs to a user and a report_date.
        // Ministry selections are stored per (user_id, eod_date).
        return $this->hasMany(MinistryInvolvement::class, 'user_id', 'user_id');
    }

    public function getMinistryInvolvementsAttribute()
    {
        if ($this->relationLoaded('ministryInvolvements')) {
            $collection = $this->getRelation('ministryInvolvements');
        } else {
            $collection = $this->ministryInvolvements()
                ->where('eod_date', $this->report_date)
                ->get();
            $this->setRelation('ministryInvolvements', $collection);
            return $collection;
        }

        $reportDate = $this->report_date ? $this->report_date->toDateString() : null;

        return $collection->filter(function ($item) use ($reportDate) {
            return $item->eod_date && $item->eod_date->toDateString() === $reportDate;
        });
    }

    public function toArray()
    {
        $attributes = parent::toArray();

        if ($this->relationLoaded('ministryInvolvements')) {
            $attributes['ministry_involvements'] = $this->ministry_involvements->values()->toArray();
        }

        return $attributes;
    }

    // Scopes

    public function scopeForManager($query, $managerId)
    {
        return $query->whereHas('user', function ($q) use ($managerId) {
            $q->where('manager_id', $managerId);
        });
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeMissing($query, $date)
    {
        // Returns users without a report for date - placeholder for service
        return $query->where('report_date', '<>', $date);
    }
}
