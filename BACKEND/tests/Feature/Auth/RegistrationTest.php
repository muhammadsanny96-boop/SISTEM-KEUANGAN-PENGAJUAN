<?php

namespace Tests\Feature\Auth;

use App\Models\Division;
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

    public function test_new_users_can_register_with_division(): void
    {
        $division = Division::create(['nama_divisi' => 'IT Support']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'division_id' => $division->id,
            'phone' => '08123456789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'division_id' => $division->id,
            'role' => 'user',
        ]);
    }

    public function test_user_cannot_register_if_division_already_has_a_head_user(): void
    {
        $division = Division::create(['nama_divisi' => 'HRD']);

        // First user (Head of Division) registers successfully
        $this->post('/register', [
            'name' => 'Kepala HRD',
            'email' => 'kadiv.hrd@example.com',
            'division_id' => $division->id,
            'phone' => '08123456781',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kadiv.hrd@example.com',
            'division_id' => $division->id,
        ]);

        // Logout before second guest registration attempt
        auth()->logout();

        // Second user attempts to register for the same division -> should fail validation
        $response = $this->post('/register', [
            'name' => 'User Kedua HRD',
            'email' => 'user2.hrd@example.com',
            'division_id' => $division->id,
            'phone' => '08123456782',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('division_id');
        $this->assertDatabaseMissing('users', [
            'email' => 'user2.hrd@example.com',
        ]);
    }
}
