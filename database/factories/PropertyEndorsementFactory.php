<?php

namespace Database\Factories;

use App\Enums\EndorsementEvent;
use App\Models\PropertyEndorsement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyEndorsement>
 */
class PropertyEndorsementFactory extends Factory
{
    protected $model = PropertyEndorsement::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'property_id' => 1,
            'event' => EndorsementEvent::Observation,
            'measure' => null,
            'description' => fake()->sentence(),
            'occurred_on' => now()->toDateString(),
        ];
    }
}
