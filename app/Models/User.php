<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'is_admin'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements OAuthenticatable
{
    public const ROLES = [
        'superadmin', 'direktur utama', 'direktur', 'komisaris', 'kacab',
        'keuangan', 'akademik', 'marketing', 'operasional', 'staff', 'pengajar',
    ];

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $user->role = match ($user->role) {
                'admin' => 'superadmin',
                'user', null => 'staff',
                default => $user->role,
            };
            $user->is_admin = $user->role === 'superadmin';
        });
    }

    public function isOmniAdmin(): bool
    {
        return $this->role === 'superadmin';
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
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
