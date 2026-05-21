<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\CalendarEvent;
use App\Models\EventAttendee;
use App\Models\Product;
use App\Models\InventoryMovement;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Clean up any previously seeded user records to prevent duplicate employee codes.
        User::whereIn('email', [
            'admin@example.com',
            'manager@example.com',
            'employee@example.com',
            'hr@example.com',
            'warehouse@example.com',
            'finance@example.com',
            'payroll@example.com',
            'field@example.com',
            'intern@example.com',
        ])->orWhereIn('employee_code', [
            'EMP-001',
            'EMP-002',
            'EMP-003',
            'EMP-004',
            'EMP-005',
            'EMP-006',
            'EMP-007',
            'EMP-008',
            'EMP-009',
        ])->delete();

        // 1. Seed Users
        $admin = User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'employee_code' => 'EMP-001',
            'name' => 'John Admin',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'position' => 'Chief Executive Officer',
            'hourly_rate' => 50.00,
            'monthly_salary' => 8000.00,
            'department' => 'Executive',
            'is_active' => true,
        ]);

        $manager = User::updateOrCreate([
            'email' => 'manager@example.com',
        ], [
            'employee_code' => 'EMP-002',
            'name' => 'Jane Manager',
            'password' => Hash::make('password'),
            'role' => 'department_manager',
            'position' => 'Engineering Manager',
            'department' => 'Engineering',
            'hourly_rate' => 40.00,
            'monthly_salary' => 6000.00,
            'is_active' => true,
        ]);

        $employee = User::updateOrCreate([
            'email' => 'employee@example.com',
        ], [
            'employee_code' => 'EMP-003',
            'name' => 'Bob Employee',
            'password' => Hash::make('password'),
            'role' => 'employee',
            'position' => 'Software Engineer',
            'department' => 'Engineering',
            'hourly_rate' => 25.00,
            'monthly_salary' => 0.00,
            'is_active' => true,
        ]);

        $hr = User::updateOrCreate([
            'email' => 'hr@example.com',
        ], [
            'employee_code' => 'EMP-004',
            'name' => 'Alice HR',
            'password' => Hash::make('password'),
            'role' => 'hr_manager',
            'position' => 'HR Manager',
            'department' => 'Human Resources',
            'hourly_rate' => 30.00,
            'monthly_salary' => 4500.00,
            'is_active' => true,
        ]);

        $warehouse = User::updateOrCreate([
            'email' => 'warehouse@example.com',
        ], [
            'employee_code' => 'EMP-005',
            'name' => 'Charlie Warehouse',
            'password' => Hash::make('password'),
            'role' => 'warehouse_manager',
            'position' => 'Warehouse Manager',
            'department' => 'Logistics',
            'hourly_rate' => 20.00,
            'monthly_salary' => 3500.00,
            'is_active' => true,
        ]);

        $finance = User::updateOrCreate([
            'email' => 'finance@example.com',
        ], [
            'employee_code' => 'EMP-006',
            'name' => 'Fiona Finance',
            'password' => Hash::make('password'),
            'role' => 'finance',
            'position' => 'Finance Specialist',
            'department' => 'Finance',
            'hourly_rate' => 45.00,
            'monthly_salary' => 7000.00,
            'is_active' => true,
        ]);

        $payroll = User::updateOrCreate([
            'email' => 'payroll@example.com',
        ], [
            'employee_code' => 'EMP-007',
            'name' => 'Peter Payroll',
            'password' => Hash::make('password'),
            'role' => 'payroll_processor',
            'position' => 'Payroll Processor',
            'department' => 'Finance',
            'hourly_rate' => 40.00,
            'monthly_salary' => 6500.00,
            'is_active' => true,
        ]);

        $fieldStaff = User::updateOrCreate([
            'email' => 'field@example.com',
        ], [
            'employee_code' => 'EMP-008',
            'name' => 'Frank Field',
            'password' => Hash::make('password'),
            'role' => 'field_staff',
            'position' => 'Field Technician',
            'department' => 'Operations',
            'hourly_rate' => 22.00,
            'monthly_salary' => 0.00,
            'is_active' => true,
        ]);

        $intern = User::updateOrCreate([
            'email' => 'intern@example.com',
        ], [
            'employee_code' => 'EMP-009',
            'name' => 'Ian Intern',
            'password' => Hash::make('password'),
            'role' => 'intern',
            'position' => 'Engineering Intern',
            'department' => 'Engineering',
            'hourly_rate' => 15.00,
            'monthly_salary' => 0.00,
            'is_active' => true,
        ]);

        // manager hierarchy
        $manager->manager_id = $admin->id;
        $manager->save();

        $employee->manager_id = $manager->id;
        $employee->save();

        $hr->manager_id = $admin->id;
        $hr->save();

        $warehouse->manager_id = $admin->id;
        $warehouse->save();

        $fieldStaff->manager_id = $manager->id;
        $fieldStaff->save();

        $intern->manager_id = $manager->id;
        $intern->save();

        // 2. Seed Attendance Records for Employee (last 7 days)
        $today = Carbon::today();
        
        // Day 1: Present, clock-in 08:55:00, clock-out 17:05:00 (8.17 hours)
        AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => $today->copy()->subDays(6)->format('Y-m-d'),
            'clock_in_time' => $today->copy()->subDays(6)->setTime(8, 55, 0),
            'clock_out_time' => $today->copy()->subDays(6)->setTime(17, 5, 0),
            'latitude' => 37.774929,
            'longitude' => -122.419416,
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        // Day 2: Late, clock-in 09:15:00 (15 late minutes), clock-out 17:30:00 (8.25 hours)
        AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => $today->copy()->subDays(5)->format('Y-m-d'),
            'clock_in_time' => $today->copy()->subDays(5)->setTime(9, 15, 0),
            'clock_out_time' => $today->copy()->subDays(5)->setTime(17, 30, 0),
            'latitude' => 37.774910,
            'longitude' => -122.419400,
            'status' => 'late',
            'late_minutes' => 15,
        ]);

        // Day 3: Present, clock-in 08:58:00, clock-out 18:02:00 (9.07 hours - includes 1.07 overtime hours)
        AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => $today->copy()->subDays(4)->format('Y-m-d'),
            'clock_in_time' => $today->copy()->subDays(4)->setTime(8, 58, 0),
            'clock_out_time' => $today->copy()->subDays(4)->setTime(18, 2, 0),
            'latitude' => 37.774929,
            'longitude' => -122.419416,
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        // Day 4: Present, clock-in 09:00:00, clock-out 17:00:00 (8.0 hours)
        AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => $today->copy()->subDays(3)->format('Y-m-d'),
            'clock_in_time' => $today->copy()->subDays(3)->setTime(9, 0, 0),
            'clock_out_time' => $today->copy()->subDays(3)->setTime(17, 0, 0),
            'latitude' => 37.774929,
            'longitude' => -122.419416,
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        // Day 5: Late, clock-in 09:45:00 (45 late minutes), clock-out 17:00:00 (7.25 hours)
        AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => $today->copy()->subDays(2)->format('Y-m-d'),
            'clock_in_time' => $today->copy()->subDays(2)->setTime(9, 45, 0),
            'clock_out_time' => $today->copy()->subDays(2)->setTime(17, 0, 0),
            'latitude' => 37.774920,
            'longitude' => -122.419410,
            'status' => 'late',
            'late_minutes' => 45,
        ]);

        // 3. Seed Products
        $prod1 = Product::create([
            'sku' => 'PROD-001',
            'name' => 'Eco-friendly Packaging Boxes',
            'current_stock' => 100,
            'min_stock_threshold' => 20,
            'cost_price' => 1.50,
        ]);

        $prod2 = Product::create([
            'sku' => 'PROD-002',
            'name' => 'Heavy Duty Bubble Wrap Roll',
            'current_stock' => 8, // Low Stock!
            'min_stock_threshold' => 15,
            'cost_price' => 12.00,
        ]);

        $prod3 = Product::create([
            'sku' => 'PROD-003',
            'name' => 'Biodegradable Shipping Bags (1000pcs)',
            'current_stock' => 2, // Low Stock!
            'min_stock_threshold' => 5,
            'cost_price' => 35.00,
        ]);

        // 4. Seed Inventory Movements
        InventoryMovement::create([
            'product_id' => $prod1->id,
            'movement_type' => 'in',
            'quantity' => 100,
            'stock_before' => 0,
            'stock_after' => 100,
            'reason' => 'Initial inventory setup',
        ]);

        InventoryMovement::create([
            'product_id' => $prod2->id,
            'movement_type' => 'in',
            'quantity' => 20,
            'stock_before' => 0,
            'stock_after' => 20,
            'reason' => 'Initial inventory setup',
        ]);

        InventoryMovement::create([
            'product_id' => $prod2->id,
            'movement_type' => 'out',
            'quantity' => 12,
            'stock_before' => 20,
            'stock_after' => 8,
            'reason' => 'Stock dispatched for shipping department',
        ]);

        InventoryMovement::create([
            'product_id' => $prod3->id,
            'movement_type' => 'in',
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'reason' => 'Initial inventory setup',
        ]);

        InventoryMovement::create([
            'product_id' => $prod3->id,
            'movement_type' => 'out',
            'quantity' => 3,
            'stock_before' => 5,
            'stock_after' => 2,
            'reason' => 'Used in warehouse packaging operations',
        ]);

        // 5. Seed Tasks
        $task1 = Task::create([
            'title' => 'Complete Employee Portal Scaffolding',
            'description' => 'Implement the backend controllers, Eloquent models, and Vite-Vue frontend scaffold for clock-in/out and timesheets.',
            'assigned_to' => $employee->id,
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => $today->copy()->addDays(3)->format('Y-m-d'),
        ]);

        $task2 = Task::create([
            'title' => 'Review Q2 Budget Draft',
            'description' => 'Analyze the financial forecast, department costs, and payroll projection spreadsheets for approval.',
            'assigned_to' => $manager->id,
            'priority' => 'medium',
            'status' => 'pending',
            'due_date' => $today->copy()->addDays(7)->format('Y-m-d'),
        ]);

        $task3 = Task::create([
            'title' => 'Update Safety Equipment Inventory',
            'description' => 'Perform a physical stock audit of warehouses, verify safety glove and vest counts, and record movements.',
            'assigned_to' => $warehouse->id,
            'priority' => 'critical',
            'status' => 'completed',
            'due_date' => $today->copy()->subDay()->format('Y-m-d'),
        ]);

        $task4 = Task::create([
            'title' => 'Organize Summer Team Building Event',
            'description' => 'Draft proposed activity list, gather venue quotes, and send out invitations to all department managers.',
            'assigned_to' => $hr->id,
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => $today->copy()->addDays(14)->format('Y-m-d'),
        ]);

        // Comments
        TaskComment::create([
            'task_id' => $task1->id,
            'user_id' => $manager->id,
            'comment' => 'Please ensure GPS coordinates are captured automatically during the clock-in/out form submission.',
        ]);

        TaskComment::create([
            'task_id' => $task1->id,
            'user_id' => $employee->id,
            'comment' => 'Working on it! Using HTML5 browser geolocation API to populate lat/long coordinates seamlessly.',
        ]);

        // 6. Seed Calendar Events
        $event1 = CalendarEvent::create([
            'title' => 'Weekly Sprint Planning',
            'description' => 'Engineering sprint kickoff, ticket allocation, and goal mapping.',
            'start_time' => $today->copy()->setTime(10, 0, 0),
            'end_time' => $today->copy()->setTime(11, 30, 0),
            'type' => 'meeting',
            'created_by' => $manager->id,
        ]);

        $event2 = CalendarEvent::create([
            'title' => 'Memorial Day Holiday',
            'description' => 'National holiday - Office closed.',
            'start_time' => $today->copy()->addWeek()->startOfWeek()->setTime(9, 0, 0),
            'end_time' => $today->copy()->addWeek()->startOfWeek()->setTime(17, 0, 0),
            'type' => 'holiday',
            'created_by' => $admin->id,
        ]);

        $event3 = CalendarEvent::create([
            'title' => 'Bob Sick Leave',
            'description' => 'Approved sick leave request.',
            'start_time' => $today->copy()->subDays(6)->setTime(9, 0, 0),
            'end_time' => $today->copy()->subDays(6)->setTime(17, 0, 0),
            'type' => 'leave',
            'created_by' => $employee->id,
        ]);

        // Event Attendees
        EventAttendee::create([
            'event_id' => $event1->id,
            'user_id' => $manager->id,
            'response' => 'accepted',
        ]);

        EventAttendee::create([
            'event_id' => $event1->id,
            'user_id' => $employee->id,
            'response' => 'accepted',
        ]);

        EventAttendee::create([
            'event_id' => $event1->id,
            'user_id' => $admin->id,
            'response' => 'pending',
        ]);

        EventAttendee::create([
            'event_id' => $event3->id,
            'user_id' => $employee->id,
            'response' => 'accepted',
        ]);
    }
}
