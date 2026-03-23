<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Person;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesValidCpf;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    use GeneratesValidCpf;

    public function run(): void
    {
        foreach (User::query()->orderBy('id')->get() as $user) {
            for ($i = 0; $i < 30; $i++) {
                $seed = $user->id * 1000 + $i;

                Person::query()->create([
                    'user_id' => $user->id,
                    'name' => fake()->name(),
                    'birth_date' => fake()->dateTimeBetween('-80 years', '-18 years'),
                    'cpf' => $this->validCpfFromSeed($seed),
                    'gender' => fake()->randomElement(Gender::cases()),
                    'phone' => fake()->optional(0.75)->numerify('###########'),
                    'email' => fake()->optional(0.5)->safeEmail(),
                ]);
            }
        }
    }
}
