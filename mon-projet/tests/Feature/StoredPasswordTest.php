<?php

namespace Tests\Feature;

use App\Models\StoredPasswordShare;
use App\Models\StoredPassword;
use App\Models\User;
use App\Notifications\PasswordShareInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StoredPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_a_website_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/passwords', [
            'service_name' => 'Google Classroom',
            'url' => 'https://classroom.google.com',
            'password' => 'WebsitePass123!',
            'notes' => 'School account',
            'favicon_url' => 'https://example.com/favicon-256.png',
        ]);

        $response->assertRedirect('/');

        $storedPassword = StoredPassword::first();

        $this->assertNotNull($storedPassword);
        $this->assertSame($user->id, $storedPassword->user_id);
        $this->assertSame('Google Classroom', $storedPassword->service_name);
        $this->assertSame('https://classroom.google.com', $storedPassword->url);
        $this->assertSame('School account', $storedPassword->notes);
        $this->assertSame('https://example.com/favicon-256.png', $storedPassword->favicon_url);
        $this->assertSame('WebsitePass123!', $storedPassword->password);

        $rawPassword = StoredPassword::query()->toBase()->value('password');

        $this->assertNotSame('WebsitePass123!', $rawPassword);
    }

    public function test_favicon_url_is_fetched_from_website_when_blank(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://youtube.com' => Http::response('
                <html>
                    <head>
                        <link rel="icon" href="/favicon.ico" sizes="32x32">
                        <link rel="icon" href="https://www.youtube.com/s/desktop/favicon_144x144.png" sizes="144x144">
                    </head>
                </html>
            '),
        ]);

        $this->actingAs($user)->post('/passwords', [
            'service_name' => 'YouTube',
            'url' => 'https://youtube.com',
            'password' => 'WebsitePass123!',
        ]);

        $storedPassword = StoredPassword::first();

        $this->assertSame('https://www.youtube.com/s/desktop/favicon_144x144.png', $storedPassword->favicon_url);
    }

    public function test_favicon_url_falls_back_when_website_icon_is_unavailable(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://laravel.com/docs' => Http::response('<html><head></head><body></body></html>'),
        ]);

        $this->actingAs($user)->post('/passwords', [
            'service_name' => 'Laravel',
            'url' => 'https://laravel.com/docs',
            'password' => 'WebsitePass123!',
        ]);

        $storedPassword = StoredPassword::first();

        $this->assertSame('https://www.google.com/s2/favicons?domain=laravel.com&sz=256', $storedPassword->favicon_url);
    }

    public function test_guest_cannot_save_a_website_password(): void
    {
        $response = $this->post('/passwords', [
            'service_name' => 'Google Classroom',
            'url' => 'https://classroom.google.com',
            'password' => 'WebsitePass123!',
        ]);

        $response->assertRedirect('/login');
        $this->assertSame(0, StoredPassword::count());
    }

    public function test_dashboard_only_shows_current_users_passwords(): void
    {
        $currentUser = User::factory()->create();
        $otherUser = User::factory()->create();

        StoredPassword::create([
            'user_id' => $currentUser->id,
            'service_name' => 'Current User Service',
            'url' => 'https://current.example.com',
            'password' => 'WebsitePass123!',
            'notes' => 'Visible note',
            'favicon_url' => 'https://current.example.com/favicon.png',
        ]);

        StoredPassword::create([
            'user_id' => $otherUser->id,
            'service_name' => 'Other User Service',
            'url' => 'https://other.example.com',
            'password' => 'OtherPass123!',
            'notes' => 'Hidden note',
            'favicon_url' => 'https://other.example.com/favicon.png',
        ]);

        $this->actingAs($currentUser)
            ->get('/')
            ->assertOk()
            ->assertSee('Current User Service')
            ->assertSee('https://current.example.com')
            ->assertSee('Visible note')
            ->assertDontSee('Other User Service')
            ->assertDontSee('https://other.example.com')
            ->assertDontSee('Hidden note');
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_can_view_password_details(): void
    {
        $user = User::factory()->create();
        $storedPassword = StoredPassword::create([
            'user_id' => $user->id,
            'service_name' => 'YouTube',
            'url' => 'https://youtube.com',
            'password' => 'VideoPass123!',
            'notes' => 'Creator account',
            'favicon_url' => 'https://youtube.com/favicon.ico',
        ]);

        $this->actingAs($user)
            ->get("/passwords/{$storedPassword->id}")
            ->assertOk()
            ->assertSee('YouTube')
            ->assertSee('https://youtube.com')
            ->assertDontSee('VideoPass123!')
            ->assertSee('Hidden until revealed')
            ->assertSee('Creator account')
            ->assertSee('Delete password?')
            ->assertSee('id="delete-confirmation-modal"', false);
    }

    public function test_user_can_reveal_their_saved_password(): void
    {
        $user = User::factory()->create();
        $storedPassword = StoredPassword::create([
            'user_id' => $user->id,
            'service_name' => 'YouTube',
            'url' => 'https://youtube.com',
            'password' => 'VideoPass123!',
        ]);

        $this->actingAs($user)
            ->postJson("/passwords/{$storedPassword->id}/reveal")
            ->assertOk()
            ->assertJson([
                'password' => 'VideoPass123!',
            ]);
    }

    public function test_user_can_update_their_saved_password(): void
    {
        $user = User::factory()->create();
        $storedPassword = StoredPassword::create([
            'user_id' => $user->id,
            'service_name' => 'Old Service',
            'url' => 'https://old.example.com',
            'password' => 'OldPass123!',
            'notes' => 'Old note',
            'favicon_url' => 'https://old.example.com/favicon.ico',
        ]);

        Http::fake([
            'https://new.example.com' => Http::response('<html><head><link rel="icon" href="/icon.png" sizes="64x64"></head></html>'),
        ]);

        $response = $this->actingAs($user)->put("/passwords/{$storedPassword->id}", [
            'service_name' => 'New Service',
            'url' => 'https://new.example.com',
            'password' => 'NewPass123!',
            'notes' => 'New note',
        ]);

        $response->assertRedirect("/passwords/{$storedPassword->id}");

        $storedPassword->refresh();

        $this->assertSame('New Service', $storedPassword->service_name);
        $this->assertSame('https://new.example.com', $storedPassword->url);
        $this->assertSame('NewPass123!', $storedPassword->password);
        $this->assertSame('New note', $storedPassword->notes);
        $this->assertSame('https://new.example.com/icon.png', $storedPassword->favicon_url);
    }

    public function test_user_can_delete_their_saved_password(): void
    {
        $user = User::factory()->create();
        $storedPassword = StoredPassword::create([
            'user_id' => $user->id,
            'service_name' => 'Delete Me',
            'url' => 'https://delete.example.com',
            'password' => 'DeletePass123!',
        ]);

        $response = $this->actingAs($user)->delete("/passwords/{$storedPassword->id}");

        $response->assertRedirect('/');
        $this->assertSame(0, StoredPassword::count());
    }

    public function test_user_cannot_manage_another_users_saved_password(): void
    {
        $currentUser = User::factory()->create();
        $otherUser = User::factory()->create();
        $storedPassword = StoredPassword::create([
            'user_id' => $otherUser->id,
            'service_name' => 'Other User Secret',
            'url' => 'https://other.example.com',
            'password' => 'OtherPass123!',
        ]);

        $this->actingAs($currentUser)->get("/passwords/{$storedPassword->id}")->assertNotFound();
        $this->actingAs($currentUser)->postJson("/passwords/{$storedPassword->id}/reveal")->assertNotFound();
        $this->actingAs($currentUser)->get("/passwords/{$storedPassword->id}/edit")->assertNotFound();
        $this->actingAs($currentUser)->put("/passwords/{$storedPassword->id}", [
            'service_name' => 'Changed',
            'url' => 'https://changed.example.com',
            'password' => 'ChangedPass123!',
        ])->assertNotFound();
        $this->actingAs($currentUser)->delete("/passwords/{$storedPassword->id}")->assertNotFound();

        $storedPassword->refresh();

        $this->assertSame('Other User Secret', $storedPassword->service_name);
    }

    public function test_owner_can_send_password_share_invitation_to_another_user(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $recipient = User::factory()->create([
            'email' => 'recipient@example.com',
        ]);
        $storedPassword = StoredPassword::create([
            'user_id' => $owner->id,
            'service_name' => 'Shared Service',
            'url' => 'https://shared.example.com',
            'password' => 'SharedPass123!',
        ]);

        $this->actingAs($owner)
            ->post("/passwords/{$storedPassword->id}/shares", [
                'recipient_email' => 'recipient@example.com',
            ])
            ->assertRedirect();

        $share = StoredPasswordShare::first();

        $this->assertNotNull($share);
        $this->assertSame($storedPassword->id, $share->stored_password_id);
        $this->assertSame($owner->id, $share->owner_id);
        $this->assertSame($recipient->id, $share->recipient_user_id);
        $this->assertSame('pending', $share->status);
        Notification::assertSentTo($recipient, PasswordShareInvitation::class);
    }

    public function test_recipient_can_accept_share_and_access_password(): void
    {
        $owner = User::factory()->create();
        $recipient = User::factory()->create([
            'email' => 'recipient@example.com',
        ]);
        $storedPassword = StoredPassword::create([
            'user_id' => $owner->id,
            'service_name' => 'Shared Service',
            'url' => 'https://shared.example.com',
            'password' => 'SharedPass123!',
        ]);
        $share = StoredPasswordShare::create([
            'stored_password_id' => $storedPassword->id,
            'owner_id' => $owner->id,
            'recipient_user_id' => $recipient->id,
            'recipient_email' => 'recipient@example.com',
            'status' => 'pending',
        ]);

        $acceptUrl = URL::temporarySignedRoute(
            'password-shares.accept',
            now()->addDays(7),
            ['share' => $share->id],
        );

        $this->actingAs($recipient)
            ->get($acceptUrl)
            ->assertRedirect("/passwords/{$storedPassword->id}");

        $share->refresh();

        $this->assertSame('accepted', $share->status);
        $this->assertNotNull($share->accepted_at);

        $this->actingAs($recipient)
            ->get('/')
            ->assertOk()
            ->assertSee('Shared Service')
            ->assertSee('Shared');

        $this->actingAs($recipient)
            ->get("/passwords/{$storedPassword->id}")
            ->assertOk()
            ->assertSee('Shared password')
            ->assertDontSee('Delete password?')
            ->assertDontSee('/edit', false);

        $this->actingAs($recipient)
            ->postJson("/passwords/{$storedPassword->id}/reveal")
            ->assertOk()
            ->assertJson([
                'password' => 'SharedPass123!',
            ]);
    }

    public function test_share_invitation_requires_registered_recipient_email(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $storedPassword = StoredPassword::create([
            'user_id' => $owner->id,
            'service_name' => 'Shared Service',
            'url' => 'https://shared.example.com',
            'password' => 'SharedPass123!',
        ]);

        $this->actingAs($owner)
            ->from("/passwords/{$storedPassword->id}")
            ->post("/passwords/{$storedPassword->id}/shares", [
                'recipient_email' => 'missing@example.com',
            ])
            ->assertRedirect("/passwords/{$storedPassword->id}")
            ->assertSessionHasErrors('recipient_email');

        $this->assertSame(0, StoredPasswordShare::count());
        Notification::assertNothingSent();
    }
}
