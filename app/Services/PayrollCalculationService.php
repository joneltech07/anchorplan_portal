<?php

namespace App\Services;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollCalculationService
{
    /**
     * Calculate and generate payroll items for a given period.
     */
    public function calculatePeriod(PayrollPeriod $period): void
    {
        DB::transaction(function () use ($period) {
            // 1. Enforce Immutability - Delete existing records for this period
            PayrollItem::where('payroll_period_id', $period->id)->delete();

            // 2. Fetch all active employees
            $users = User::where('is_active', true)->get();

            $startDate = Carbon::parse($period->start_date)->startOfDay();
            $endDate = Carbon::parse($period->end_date)->endOfDay();

            foreach ($users as $user) {
                // Fetch attendance records in period range
                $records = AttendanceRecord::where('user_id', $user->id)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->get();

                $regularHours = 0.00;
                $overtimeHours = 0.00;
                $totalLateMinutes = 0;

                foreach ($records as $record) {
                    $totalLateMinutes += $record->late_minutes;

                    if ($record->clock_in_time && $record->clock_out_time) {
                        $inTime = Carbon::parse($record->clock_in_time);
                        $outTime = Carbon::parse($record->clock_out_time);
                        
                        $secondsWorked = $outTime->diffInSeconds($inTime);
                        $hoursWorked = round($secondsWorked / 3600, 2);

                        // 8 hours standard work day limit
                        if ($hoursWorked > 8.0) {
                            $regularHours += 8.0;
                            $overtimeHours += ($hoursWorked - 8.0);
                        } else {
                            $regularHours += $hoursWorked;
                        }
                    }
                }

                // Base Pay calculation
                $basePay = 0.00;
                if ($user->hourly_rate > 0) {
                    // Hourly employee
                    $basePay = ($regularHours * $user->hourly_rate) + ($overtimeHours * $user->hourly_rate * 1.5);
                } else if ($user->monthly_salary > 0) {
                    // Salaried employee (base pay is monthly salary)
                    $basePay = $user->monthly_salary;
                }

                // Deductions calculation (e.g. late penalty: $0.50 per late minute)
                $deductions = round($totalLateMinutes * 0.50, 2);

                // Ensure deductions do not exceed base pay
                if ($deductions > $basePay) {
                    $deductions = $basePay;
                }

                $netPay = $basePay - $deductions;

                // Create immutable payroll ledger item
                PayrollItem::create([
                    'payroll_period_id' => $period->id,
                    'user_id' => $user->id,
                    'regular_hours' => $regularHours,
                    'overtime_hours' => $overtimeHours,
                    'base_pay' => $basePay,
                    'deductions' => $deductions,
                    'net_pay' => $netPay,
                ]);
            }

            // Update period status to processed
            $period->update(['status' => 'processed']);
        });
    }
}
