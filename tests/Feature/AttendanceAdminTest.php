<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 6, 12, 9, 0, 0)); // Lock time for deterministic testing
    }

    public function test_non_super_admin_cannot_edit_timesheet(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $manager = User::factory()->create(['role' => 'manager']);
        
        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 08:50:00',
            'clock_out_time' => '2026-06-11 17:15:00',
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        $response = $this
            ->actingAs($manager)
            ->put(route('attendance.edit-admin', $record->id), [
                'clock_in_time' => '2026-06-11 09:15:00',
                'clock_out_time' => '2026-06-11 17:15:00',
                'password' => 'password',
            ]);

        $response->assertStatus(403);
    }

    public function test_super_admin_can_edit_timesheet_with_correct_password(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => bcrypt('secret-password'),
        ]);

        $employee = User::factory()->create(['role' => 'employee']);

        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 08:50:00',
            'clock_out_time' => '2026-06-11 17:15:00',
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->put(route('attendance.edit-admin', $record->id), [
                'clock_in_time' => '2026-06-11 09:20:00',
                'clock_out_time' => '2026-06-11 17:30:00',
                'password' => 'secret-password',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $record->refresh();
        $this->assertEquals(Carbon::parse('2026-06-11 09:20:00'), $record->clock_in_time);
        $this->assertEquals(Carbon::parse('2026-06-11 17:30:00'), $record->clock_out_time);
    }

    public function test_super_admin_cannot_edit_timesheet_with_wrong_password(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => bcrypt('secret-password'),
        ]);

        $employee = User::factory()->create(['role' => 'employee']);

        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 08:50:00',
            'clock_out_time' => '2026-06-11 17:15:00',
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->from('/attendance')
            ->put(route('attendance.edit-admin', $record->id), [
                'clock_in_time' => '2026-06-11 09:20:00',
                'clock_out_time' => '2026-06-11 17:30:00',
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('password');
        $response->assertRedirect('/attendance');

        $record->refresh();
        $this->assertEquals(Carbon::parse('2026-06-11 08:50:00'), $record->clock_in_time);
    }

    public function test_recalculate_status_and_late_minutes_on_edit(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => bcrypt('secret-password'),
        ]);

        $employee = User::factory()->create(['role' => 'employee']);

        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 08:50:00', // On time (default shift start is 09:00:00)
            'clock_out_time' => '2026-06-11 17:00:00',
            'status' => 'present',
            'late_minutes' => 0,
        ]);

        // Shift is parsed dynamically. Default shift starts at 09:00:00.
        // Changing clock in to 09:15:00 should result in 15 late minutes and late status.
        $response = $this
            ->actingAs($superAdmin)
            ->put(route('attendance.edit-admin', $record->id), [
                'clock_in_time' => '2026-06-11 09:15:00',
                'clock_out_time' => '2026-06-11 17:00:00',
                'password' => 'secret-password',
            ]);

        $response->assertSessionHasNoErrors();
        $record->refresh();
        $this->assertEquals('late', $record->status);
        $this->assertEquals(15, $record->late_minutes);
    }

    public function test_super_admin_can_delete_timesheet_with_correct_password(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'password' => bcrypt('secret-password'),
        ]);

        $employee = User::factory()->create(['role' => 'employee']);

        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 08:50:00',
            'status' => 'present',
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->delete(route('attendance.delete-admin', $record->id), [
                'password' => 'secret-password',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertNull(AttendanceRecord::find($record->id));
    }

    public function test_non_super_admin_cannot_delete_timesheet(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $employee = User::factory()->create(['role' => 'employee']);

        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 08:50:00',
            'status' => 'present',
        ]);

        $response = $this
            ->actingAs($manager)
            ->delete(route('attendance.delete-admin', $record->id), [
                'password' => 'password',
            ]);

        $response->assertStatus(403);
        $this->assertNotNull(AttendanceRecord::find($record->id));
    }

    public function test_payment_and_duration_deduct_break_and_calculate_night_diff(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'hourly_rate' => 63.125,
        ]);

        // Shift is 2026-06-11 13:30:00 UTC to 2026-06-11 21:00:00 UTC (9:30 PM to 5:00 AM Manila, 7.5 hours elapsed, 1.0 hour break)
        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 13:30:00',
            'clock_out_time' => '2026-06-11 21:00:00',
            'status' => 'present',
        ]);

        // Actual hours should be 7.5 - 1.0 break = 6.5 hours
        $this->assertEquals(6.5, $record->getActualHours());

        // Night differential hours should be 7.0 hours (10:00 PM to 5:00 AM)
        $this->assertEquals(7.0, $record->getNightDifferentialHours());

        // Estimated Payment should be 0.5 * 63.125 + (7.0 - 1.0) * (63.125 * 1.1) = 448.19
        $this->assertEquals(448.19, $record->getEstimatedPayment());
    }

    public function test_payment_cristy_9pm_to_5am_example(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'hourly_rate' => 63.13,
        ]);

        // Shift is 2026-06-11 13:00:00 UTC to 2026-06-11 21:00:00 UTC (9:00 PM to 5:00 AM Manila, 8.0 hours elapsed, 1.0 hour break)
        $record = AttendanceRecord::create([
            'user_id' => $employee->id,
            'date' => '2026-06-11',
            'clock_in_time' => '2026-06-11 13:00:00',
            'clock_out_time' => '2026-06-11 21:00:00',
            'status' => 'present',
        ]);

        // Actual hours should be 8.0 - 1.0 break = 7.0 hours
        $this->assertEquals(7.0, $record->getActualHours());

        // Night differential hours should be 7.0 hours (10:00 PM to 5:00 AM)
        $this->assertEquals(7.0, $record->getNightDifferentialHours());

        // Estimated Payment should be 63.13 + (7.0 - 1.0) * (63.13 * 1.1) = 63.13 + 6.0 * 69.443 = 63.13 + 416.658 = 479.79
        $this->assertEquals(479.79, $record->getEstimatedPayment());
    }
}
