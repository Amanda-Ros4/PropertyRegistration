<?php

namespace Database\Seeders;

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
            ['name' => 'Amanda', 'email' => 'amanda.darosadelima@gmail.com', 'cpf_seed' => 1],
            ['name' => 'Usuário Dois', 'email' => 'usuario2@example.com', 'cpf_seed' => 2],
            ['name' => 'Usuário Três', 'email' => 'usuario3@example.com', 'cpf_seed' => 3],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'cpf' => $this->validCpfFromSeed($account['cpf_seed']),
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
