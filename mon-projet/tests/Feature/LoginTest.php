<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ]);

        $response = $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->get('/')
            ->assertSee('Test Student')
            ->assertSee('Logout')
            ->assertDontSee('Login')
            ->assertDontSee('Register');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'student@example.com',
            'password' => 'WrongPass123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
