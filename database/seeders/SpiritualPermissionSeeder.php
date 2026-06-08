<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpiritualPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_all_spiritual_records',
            'monitor_devotionals',
            'monitor_wednesday_prayer',
            'monitor_sunday_service',
            'send_spiritual_reminders',
            'view_spiritual_reports',
            'manage_cell_groups',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }

        $role = Role::firstOrCreate([
            'name' => 'pastoral_lead',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);
    }
}

