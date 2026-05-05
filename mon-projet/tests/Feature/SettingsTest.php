<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_view_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/settings')
            ->assertOk()
            ->assertSee('Account security')
            ->assertSee('Multi-factor authentication')
            ->assertSee('Modify password')
            ->assertSee('Passkeys')
            ->assertSee('Sessions');
    }

    public function test_user_can_update_master_password(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPass123!',
        ]);

        $this->actingAs($user)
            ->put('/settings/password', [
                'current_password' => 'StrongPass123!',
                'password' => 'NewStrongPass123!',
                'password_confirmation' => 'NewStrongPass123!',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('NewStrongPass123!', $user->fresh()->password));
    }

    public function test_user_can_enable_authenticator_app_mfa(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPass123!',
        ]);

        $response = $this->actingAs($user)
            ->put('/settings/mfa', [
                'current_password' => 'StrongPass123!',
                'mfa_method' => 'token',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('totp_secret');
        $response->assertSessionHas('totp_uri');

        $this->assertSame('token', $user->fresh()->mfa_method);
        $this->assertNotNull($user->fresh()->mfa_totp_secret);
        $this->assertNull($user->fresh()->mfa_token_hash);
    }

    public function test_user_cannot_disable_mfa(): void
    {
        $user = User::factory()->create([
            'password' => 'StrongPass123!',
            'mfa_method' => 'email',
        ]);

        $this->actingAs($user)
            ->from('/settings')
            ->put('/settings/mfa', [
                'current_password' => 'StrongPass123!',
                'mfa_method' => 'off',
            ])
            ->assertRedirect('/settings')
            ->assertSessionHasErrors('mfa_method');

        $this->assertSame('email', $user->fresh()->mfa_method);
    }

    public function test_user_can_revoke_another_session(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'other-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature Test Browser',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($user)
            ->delete('/settings/sessions/other-session-id')
            ->assertRedirect();

        $this->assertDatabaseMissing('sessions', [
            'id' => 'other-session-id',
        ]);
    }
}
