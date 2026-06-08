<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinistryInvolvement extends Model
{
    use HasFactory;

    protected $table = 'ministry_involvement';

    protected $fillable = [
        'user_id',
        'eod_date',
        'ministry_type',
        'custom_description',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'eod_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

