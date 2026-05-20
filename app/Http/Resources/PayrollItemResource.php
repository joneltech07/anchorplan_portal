<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_period' => new PayrollPeriodResource($this->whenLoaded('payrollPeriod') ?: $this->payrollPeriod),
            'user' => new UserResource($this->whenLoaded('user') ?: $this->user),
            'regular_hours' => (float) $this->regular_hours,
            'overtime_hours' => (float) $this->overtime_hours,
            'base_pay' => (float) $this->base_pay,
            'deductions' => (float) $this->deductions,
            'net_pay' => (float) $this->net_pay,
        ];
    }
}
