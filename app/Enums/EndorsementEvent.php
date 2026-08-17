<?php

namespace App\Enums;

enum EndorsementEvent: string
{
    case IncreaseInBuiltArea = 'I';
    case DecreaseInBuiltArea = 'D';
    case Observation = 'O';
    case Cancellation = 'C';
    case Reactivation = 'R';

    public function labelKey(): string
    {
        return match ($this) {
            self::IncreaseInBuiltArea => 'properties.endorsements.events.increase_in_built_area',
            self::DecreaseInBuiltArea => 'properties.endorsements.events.decrease_in_built_area',
            self::Observation => 'properties.endorsements.events.observation',
            self::Cancellation => 'properties.endorsements.events.cancellation',
            self::Reactivation => 'properties.endorsements.events.reactivation',
        };
    }

    public function requiresMeasure(): bool
    {
        return match ($this) {
            self::IncreaseInBuiltArea, self::DecreaseInBuiltArea => true,
            default => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
