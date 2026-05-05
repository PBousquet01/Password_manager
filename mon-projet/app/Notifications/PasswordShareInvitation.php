<?php

namespace App\Notifications;

use App\Models\StoredPasswordShare;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class PasswordShareInvitation extends Notification
{
    use Queueable;

    public function __construct(
        public readonly StoredPasswordShare $share,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'password-shares.accept',
            now()->addDays(7),
            ['share' => $this->share->id],
        );

        return (new MailMessage)
            ->subject('Password shared with you')
            ->line($this->share->owner->name.' shared a password with you in Dragon\'s Hoard.')
            ->line('Service: '.$this->share->storedPassword->service_name)
            ->action('Accept Shared Password', $url)
            ->line('You must sign in with this email address to accept the share.');
    }
}
