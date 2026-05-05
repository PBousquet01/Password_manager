<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_with_a_hashed_master_password(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/email/verify');

        $user = User::where('email', 'student@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));
        $this->assertNotSame('StrongPass123!', $user->password);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('email', $user->mfa_method);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_it_rejects_a_weak_master_password(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertSame(0, User::count());
    }

    public function test_it_rejects_a_duplicate_email_address(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
        ]);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Second Student',
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'student@example.com')->count());
    }
}
