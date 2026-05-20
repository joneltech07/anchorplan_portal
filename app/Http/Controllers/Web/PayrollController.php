<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display the payroll listings.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $periods = [];
        $employeeSlips = [];

        if ($user->hasRole(['admin', 'hr'])) {
            $periods = PayrollPeriod::orderBy('end_date', 'desc')->get();
        } else {
            // Normal employee sees their own payslip items
            $employeeSlips = PayrollItem::with('payrollPeriod')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return Inertia::render('Payroll/Index', [
            'periods' => $periods,
            'employeeSlips' => $employeeSlips,
        ]);
    }

    /**
     * Store new payroll period.
     */
    public function storePeriod(Request $request)
    {
        $user = $request->user();
        if (! $user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => 'required|string|unique:payroll_periods',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        PayrollPeriod::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
        ]);

        return back()->with('success', 'Payroll period created successfully!');
    }

    /**
     * Recalculate payroll items.
     */
    public function calculate(Request $request, string $id)
    {
        $user = $request->user();
        if (! $user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized.');
        }

        $period = PayrollPeriod::findOrFail($id);
        $this->payrollService->calculatePeriod($period);

        return back()->with('success', 'Payroll generated and locked successfully!');
    }

    /**
     * Export payroll details to CSV.
     */
    public function exportCsv(Request $request, string $id)
    {
        $user = $request->user();
        if (! $user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized.');
        }

        $period = PayrollPeriod::findOrFail($id);
        $items = PayrollItem::with('user')->where('payroll_period_id', $period->id)->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=payroll_{$period->name}.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'Employee Code',
                'Name',
                'Email',
                'Department',
                'Hourly Rate',
                'Monthly Salary',
                'Regular Hours',
                'Overtime Hours',
                'Base Pay',
                'Deductions',
                'Net Pay',
            ]);

            // CSV Data Rows
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->user->employee_code,
                    $item->user->name,
                    $item->user->email,
                    $item->user->department,
                    $item->user->hourly_rate,
                    $item->user->monthly_salary,
                    $item->regular_hours,
                    $item->overtime_hours,
                    $item->base_pay,
                    $item->deductions,
                    $item->net_pay,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get specific period details (for Inertia modal or page).
     */
    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $period = PayrollPeriod::findOrFail($id);

        if (! $user->hasRole(['admin', 'hr'])) {
            abort(403, 'Unauthorized.');
        }

        $items = PayrollItem::with('user')
            ->where('payroll_period_id', $period->id)
            ->get();

        return response()->json([
            'period' => $period,
            'items' => $items,
        ]);
    }
}
