<?php

namespace App\Enums;

enum UserProfile: string
{
    case TiAdmin = 'T';
    case SystemAdmin = 'S';
    case Attendant = 'A';

    public function labelKey(): string
    {
        return match ($this) {
            self::TiAdmin => 'users.profiles.ti_admin',
            self::SystemAdmin => 'users.profiles.system_admin',
            self::Attendant => 'users.profiles.attendant',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
