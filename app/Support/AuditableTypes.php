<?php

namespace App\Support;

use App\Models\Person;
use App\Models\Property;
use App\Models\PropertyEndorsement;
use App\Models\User;

class AuditableTypes
{
    private const MAP = [
        User::class => 'users',
        Person::class => 'people',
        Property::class => 'properties',
        PropertyEndorsement::class => 'property_endorsements',
    ];

    public static function classes(): array
    {
        return array_keys(self::MAP);
    }

    public static function labelKey(string $type): string
    {
        $key = self::MAP[$type] ?? str(class_basename($type))->snake()->value();

        return 'audit.tables.'.$key;
    }

    public static function options(): array
    {
        return array_map(
            fn (string $class) => [
                'value' => $class,
                'label_key' => self::labelKey($class),
            ],
            self::classes(),
        );
    }
}
