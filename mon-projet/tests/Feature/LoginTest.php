<?php

namespace Tests\Feature;

use App\Notifications\MfaEmailCode;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
            'mfa_method' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
            'mfa_method' => null,
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

    public function test_user_with_authenticator_app_mfa_must_complete_challenge(): void
    {
        $totpService = app(TotpService::class);
        $secret = $totpService->generateSecret();
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
            'mfa_method' => 'token',
            'mfa_totp_secret' => $secret,
        ]);

        $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ])->assertRedirect('/mfa/challenge');

        $this->assertGuest();

        $this->post('/mfa/challenge', [
            'code' => $totpService->code($secret),
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_with_default_email_mfa_receives_a_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
            'mfa_method' => 'email',
        ]);

        $this->post('/login', [
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
        ])->assertRedirect('/mfa/challenge');

        Notification::assertSentTo($user, MfaEmailCode::class);
        $this->assertNotNull($user->fresh()->mfa_email_code_hash);
        $this->assertGuest();
    }
}
