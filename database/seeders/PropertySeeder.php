<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $user = User::query()->inRandomOrder()->first();
            if (! $user) {
                return;
            }

            $person = Person::query()
                ->where('user_id', $user->id)
                ->inRandomOrder()
                ->first();

            if (! $person) {
                continue;
            }

            Property::query()->create([
                'user_id' => $user->id,
                'person_id' => $person->id,
                'street' => fake()->streetName(),
                'number' => (string) fake()->buildingNumber(),
                'neighborhood' => fake()->city(),
                'complement' => fake()->optional(0.35)->secondaryAddress(),
            ]);
        }
    }
}
