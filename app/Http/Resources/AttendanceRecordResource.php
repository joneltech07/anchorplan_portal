<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user') ?: $this->user),
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'clock_in_time' => $this->clock_in_time ? $this->clock_in_time->toIso8601String() : null,
            'clock_out_time' => $this->clock_out_time ? $this->clock_out_time->toIso8601String() : null,
            'latitude' => $this->latitude ? (float) $this->latitude : null,
            'longitude' => $this->longitude ? (float) $this->longitude : null,
            'status' => $this->status,
            'late_minutes' => (int) $this->late_minutes,
        ];
    }
}
