<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'sex' => 'Male',
            'birth_date' => '1995-01-01',
            'birth_place' => 'Manila',
            'nationality' => 'Filipino',
            'civil_status' => 'Single',
            'current_address' => '123 Main St, Manila',
            'contact_number' => '09171234567',
            'father_full_name' => 'John Doe Sr',
            'father_occupation' => 'Engineer',
            'mother_full_name' => 'Jane Doe',
            'mother_occupation' => 'Teacher',
            'guardian' => 'Jane Doe',
            'guardian_contact_number' => '09171234567',
            'date_employed' => '2026-01-01',
            'employee_status' => 'Full Time',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
