<?php

namespace App\Policies;

use App\Models\DevotionalRecord;
use App\Models\User;

class SpiritualPolicy
{
    /**
     * Admin/Super Admin + Pastoral Lead can view and edit everything.
     * HR Manager can view all, edit devotionals ONLY.
     * Department Manager can view only their department, read-only.
     */
    public function viewAllSpiritualRecords(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'super_admin',
            'pastoral_lead',
            'hr_manager',
            'department_manager',
        ]);
    }

    public function updateDevotional(User $user, ?DevotionalRecord $record = null): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin', 'pastoral_lead'])) {
            return true;
        }

        if ($user->hasRole('hr_manager')) {
            return true;
        }

        // Department Manager: read-only
        return false;
    }

    public function updatePrayerAttendance(User $user): bool
    {
        // Admin/Super Admin + Pastoral Lead can edit prayer/service.
        return $user->hasAnyRole(['admin', 'super_admin', 'pastoral_lead']);
    }
}



