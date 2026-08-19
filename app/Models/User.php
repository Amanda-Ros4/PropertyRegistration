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
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class User extends Authenticatable implements Auditable
{
    use AuditableTrait;
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

    protected $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
        if ($this->profile === UserProfile::TiAdmin) {
            return true;
        }

        return $this->getRawOriginal('profile') === UserProfile::TiAdmin->value;
    }

    public function isSystemAdmin(): bool
    {
        if ($this->profile === UserProfile::SystemAdmin) {
            return true;
        }

        return $this->getRawOriginal('profile') === UserProfile::SystemAdmin->value;
    }

    public function isAttendant(): bool
    {
        if ($this->profile === UserProfile::Attendant) {
            return true;
        }

        return $this->getRawOriginal('profile') === UserProfile::Attendant->value;
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
        return $this->isTiAdmin() || $this->isSystemAdmin();
    }

    public function canAccessAllRecords(): bool
    {
        return $this->isTiAdmin();
    }

    public function canAssignProfile(UserProfile $profile): bool
    {
        if ($this->isTiAdmin()) {
            return true;
        }

        return $this->isSystemAdmin() && $profile === UserProfile::Attendant;
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
