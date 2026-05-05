<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_generator_to_login(): void
    {
        $this->get('/generator')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_generator(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/generator')
            ->assertOk()
            ->assertSee('Forge a strong password')
            ->assertSee('Symbols')
            ->assertSee('Numbers')
            ->assertSee('Uppercase')
            ->assertSee('Lowercase')
            ->assertSee('Entropy');
    }
}
