<?php

namespace App\Enums;

enum PropertyType: string
{
    case Land = 'land';
    case House = 'house';
    case Apartment = 'apartment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
