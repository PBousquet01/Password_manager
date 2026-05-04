<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user_with_a_hashed_master_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'terms' => 'on',
        ]);

        $response->assertRedirect('/');

        $user = User::where('email', 'student@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));
        $this->assertNotSame('StrongPass123!', $user->password);
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
}
