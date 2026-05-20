<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can access the tasks view
    }

    /**
     * Determine whether the user can view a task.
     */
    public function view(User $user, Task $task): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update (edit/reassign) the task.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can change status.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can add comments.
     */
    public function comment(User $user, Task $task): bool
    {
        return true; // All authenticated users can comment
    }
}
