<?php

namespace App\Policies;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\User;

class PayrollPolicy
{
    /**
     * Determine whether the user can view payroll listings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'hr', 'employee', 'manager']);
    }

    /**
     * Determine whether the user can manage (create, update, recalculate) payroll periods.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can view a specific payroll item.
     */
    public function viewItem(User $user, PayrollItem $item): bool
    {
        return $user->hasRole(['admin', 'hr']) || $user->id === $item->user_id;
    }
}
