<?php

namespace Database\Seeders;

use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesValidCpf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use GeneratesValidCpf;

    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Administrador TI',
                'email' => 'ti@example.com',
                'profile' => UserProfile::TiAdmin,
                'cpf_seed' => 1,
            ],
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@example.com',
                'profile' => UserProfile::SystemAdmin,
                'cpf_seed' => 2,
            ],
            [
                'name' => 'Atendente',
                'email' => 'atendente@example.com',
                'profile' => UserProfile::Attendant,
                'cpf_seed' => 3,
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'cpf' => $this->validCpfFromSeed($account['cpf_seed']),
                    'password' => Hash::make('password'),
                    'profile' => $account['profile'],
                    'active' => ActiveStatus::Active,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
