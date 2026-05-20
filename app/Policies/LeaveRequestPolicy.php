<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'hr']) || $user->hasRole('employee');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole(['admin', 'manager', 'hr']) || $user->id === $leaveRequest->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'hr', 'employee']);
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole(['admin', 'manager', 'hr']);
    }
}
