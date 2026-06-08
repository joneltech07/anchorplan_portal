<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'hr_manager',
            'finance',
            'general_manager',
            'department_manager',
            'warehouse_manager',
            'warehouse_staff',
            'team_lead',
            'employee',
            'field_staff',
            'intern',
            'payroll_processor',
            'executive_assistant',
            'ceo',
            'cto',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
