<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskComment;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole(['admin', 'manager'])) {
            $tasks = Task::with(['assignee', 'comments.user'])->orderBy('priority', 'desc')->orderBy('due_date')->get();
        } else {
            $tasks = Task::with(['assignee', 'comments.user'])
                ->where('assigned_to', $user->id)
                ->orWhere('created_at', '!=', null)
                ->orderBy('priority', 'desc')
                ->orderBy('due_date')
                ->get();
        }

        $team = User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'role']);

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'team' => $team,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
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

        return back()->with('success', 'Task created successfully.');
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

        if (isset($validated['assigned_to']) && $task->assigned_to !== $validated['assigned_to']) {
            $assignee = User::find($validated['assigned_to']);
            if ($assignee) {
                Notification::send($assignee, new TaskAssignedNotification($task));
            }
        }

        return back()->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,review,completed',
        ]);

        $task->update($validated);

        return back()->with('success', 'Task status updated.');
    }

    public function comment(Request $request, Task $task)
    {
        $this->authorize('comment', $task);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Comment added.');
    }
}
