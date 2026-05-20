<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time ? $this->start_time->toIso8601String() : null,
            'end_time' => $this->end_time ? $this->end_time->toIso8601String() : null,
            'type' => $this->type,
            'creator' => new UserResource($this->whenLoaded('creator') ?: $this->creator),
            'attendees' => $this->attendees->map(fn($attendee) => [
                'user_id' => $attendee->user_id,
                'name' => $attendee->user ? $attendee->user->name : 'Unknown',
                'email' => $attendee->user ? $attendee->user->email : '',
                'response' => $attendee->response,
            ]),
        ];
    }
}
