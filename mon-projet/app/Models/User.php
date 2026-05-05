<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'mfa_method',
    'mfa_email_code_hash',
    'mfa_email_code_expires_at',
    'mfa_token_hash',
    'mfa_totp_secret',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function storedPasswords(): HasMany
    {
        return $this->hasMany(StoredPassword::class);
    }

    public function passkeys(): HasMany
    {
        return $this->hasMany(Passkey::class);
    }

    public function acceptedPasswordShares(): HasMany
    {
        return $this->hasMany(StoredPasswordShare::class, 'recipient_user_id')
            ->where('status', 'accepted');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mfa_email_code_expires_at' => 'datetime',
            'mfa_totp_secret' => 'encrypted',
            'password' => 'hashed',
        ];
    }
}
