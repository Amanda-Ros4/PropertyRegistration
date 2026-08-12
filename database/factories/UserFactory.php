<?php

namespace Database\Factories;

use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesValidCpf;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Features;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    use GeneratesValidCpf;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $cpfSeed = 1000;

        return [
            'name' => fake()->name(),
            'cpf' => $this->validCpfFromSeed(++$cpfSeed),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'profile' => UserProfile::Attendant,
            'active' => ActiveStatus::Active,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'remember_token' => Str::random(10),
            'profile_photo_path' => null,
            'current_team_id' => null,
        ];
    }

    public function tiAdmin(): static
    {
        return $this->state(fn () => ['profile' => UserProfile::TiAdmin]);
    }

    public function systemAdmin(): static
    {
        return $this->state(fn () => ['profile' => UserProfile::SystemAdmin]);
    }

    public function attendant(): static
    {
        return $this->state(fn () => ['profile' => UserProfile::Attendant]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active' => ActiveStatus::Inactive]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a personal team.
     */
    public function withPersonalTeam(?callable $callback = null): static
    {
        if (! Features::hasTeamFeatures()) {
            return $this->state([]);
        }

        return $this->has(
            Team::factory()
                ->state(fn (array $attributes, User $user) => [
                    'name' => $user->name.'\'s Team',
                    'user_id' => $user->id,
                    'personal_team' => true,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}
