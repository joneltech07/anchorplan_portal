<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'hr']);
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, CalendarEvent $event): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->id === $event->created_by;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, CalendarEvent $event): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->id === $event->created_by;
    }
}
