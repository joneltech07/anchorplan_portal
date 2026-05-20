<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'regular_hours',
        'overtime_hours',
        'base_pay',
        'deductions',
        'net_pay',
    ];

    protected function casts(): array
    {
        return [
            'regular_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'base_pay' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
        ];
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
