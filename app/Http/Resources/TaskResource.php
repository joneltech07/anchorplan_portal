<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'assignee' => new UserResource($this->whenLoaded('assignee') ?: $this->assignee),
            'priority' => $this->priority, // 'low', 'medium', 'high', 'critical'
            'status' => $this->status, // 'pending', 'in_progress', 'review', 'completed'
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'comments' => TaskCommentResource::collection($this->whenLoaded('comments') ?: $this->comments),
        ];
    }
}
