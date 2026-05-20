<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', // e.g. "May 2026"
        'start_date',
        'end_date',
        'status', // 'draft', 'processed', 'paid'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
        ];
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }
}
