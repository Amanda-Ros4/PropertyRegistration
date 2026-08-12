<?php

namespace App\Enums;

enum ActiveStatus: string
{
    case Active = 'S';
    case Inactive = 'N';

    public function labelKey(): string
    {
        return match ($this) {
            self::Active => 'users.active_status.active',
            self::Inactive => 'users.active_status.inactive',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
