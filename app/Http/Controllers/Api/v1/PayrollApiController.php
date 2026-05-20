<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayrollPeriodResource;
use App\Http\Resources\PayrollItemResource;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;

class PayrollApiController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Get payroll history. For employees, their own slips. For HR, all slips.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(['admin', 'hr'])) {
            $periods = PayrollPeriod::orderBy('end_date', 'desc')->get();
            return PayrollPeriodResource::collection($periods);
        }

        // Return employee's own items
        $items = PayrollItem::with('payrollPeriod')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return PayrollItemResource::collection($items);
    }

    /**
     * Create a payroll period (HR/Admin only).
     */
    public function storePeriod(Request $request)
    {
        if ($request->user()->cannot('manage', PayrollPeriod::class)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:payroll_periods',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $period = PayrollPeriod::create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'Payroll period created!',
            'period' => new PayrollPeriodResource($period),
        ], 210);
    }

    /**
     * Calculate/Recalculate period items (HR/Admin only).
     */
    public function calculate(Request $request, string $id)
    {
        if ($request->user()->cannot('manage', PayrollPeriod::class)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $period = PayrollPeriod::findOrFail($id);

        $this->payrollService->calculatePeriod($period);

        return response()->json([
            'message' => 'Payroll generated successfully for this period.',
            'period' => new PayrollPeriodResource($period),
        ]);
    }

    /**
     * Get items in a period.
     */
    public function periodItems(Request $request, string $id)
    {
        $user = $request->user();
        $period = PayrollPeriod::findOrFail($id);

        if ($user->hasRole(['admin', 'hr'])) {
            $items = PayrollItem::with('user')->where('payroll_period_id', $period->id)->get();
            return PayrollItemResource::collection($items);
        }

        // Employee can only see their own
        $item = PayrollItem::with('user')
            ->where('payroll_period_id', $period->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return new PayrollItemResource($item);
    }
}
