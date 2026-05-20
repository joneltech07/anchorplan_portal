<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskCommentResource;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TaskApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tasks = Task::with(['assignee', 'comments.user'])
            ->when(! $user->hasRole(['admin', 'manager']), fn ($query) => $query->where('assigned_to', $user->id))
            ->orderBy('priority', 'desc')
            ->orderBy('due_date')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create(array_merge($validated, ['status' => 'pending']));

        $assignee = User::find($validated['assigned_to']);
        if ($assignee) {
            Notification::send($assignee, new TaskAssignedNotification($task));
        }

        return new TaskResource($task->load(['assignee', 'comments.user']));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'sometimes|required|exists:users,id',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'status' => 'sometimes|required|in:pending,in_progress,review,completed',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        if (isset($validated['assigned_to'])) {
            $assignee = User::find($validated['assigned_to']);
            if ($assignee) {
                Notification::send($assignee, new TaskAssignedNotification($task));
            }
        }

        return new TaskResource($task->load(['assignee', 'comments.user']));
    }

    public function comment(Request $request, Task $task)
    {
        $this->authorize('comment', $task);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        return new TaskCommentResource($comment->load('user'));
    }
}
