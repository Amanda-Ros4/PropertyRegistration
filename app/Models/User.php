<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
        'profile',
        'active',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function isTiAdmin(): bool
    {
        return $this->profile === UserProfile::TiAdmin;
    }

    public function isSystemAdmin(): bool
    {
        return $this->profile === UserProfile::SystemAdmin;
    }

    public function isAttendant(): bool
    {
        return $this->profile === UserProfile::Attendant;
    }

    public function isActive(): bool
    {
        return $this->active === ActiveStatus::Active;
    }

    public function canManageUsers(): bool
    {
        return $this->isTiAdmin() || $this->isSystemAdmin();
    }

    public function canViewAudit(): bool
    {
        return ! $this->isAttendant();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile' => UserProfile::class,
            'active' => ActiveStatus::class,
        ];
    }
}
