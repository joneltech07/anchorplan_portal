<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'hourly_rate' => (float) $this->hourly_rate,
            'monthly_salary' => (float) $this->monthly_salary,
            'department' => $this->department,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
