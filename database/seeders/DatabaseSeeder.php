<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Person;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $people = collect([
            [
                'name' => 'João Silva Santos',
                'birth_date' => '1985-03-15',
                'cpf' => '529.982.247-25',
                'gender' => Gender::Male,
                'phone' => '(11) 98765-4321',
                'email' => 'joao.silva@example.com',
            ],
            [
                'name' => 'Maria Oliveira Costa',
                'birth_date' => '1990-07-22',
                'cpf' => '853.940.520-63',
                'gender' => Gender::Female,
                'phone' => '(21) 99876-5432',
                'email' => 'maria.oliveira@example.com',
            ],
            [
                'name' => 'Carlos Pereira Lima',
                'birth_date' => '1978-11-08',
                'cpf' => '111.444.777-35',
                'gender' => Gender::Male,
                'phone' => null,
                'email' => null,
            ],
        ])->map(fn ($data) => Person::firstOrCreate(
            ['user_id' => $user->id, 'cpf' => $data['cpf']],
            [...$data, 'user_id' => $user->id]
        ));

        $addresses = [
            ['street' => 'Rua das Flores', 'number' => '123', 'neighborhood' => 'Jardim Primavera', 'complement' => 'Apto 4B'],
            ['street' => 'Avenida Brasil', 'number' => '456', 'neighborhood' => 'Centro', 'complement' => null],
            ['street' => 'Rua São João', 'number' => '789', 'neighborhood' => 'Vila Nova', 'complement' => 'Casa'],
            ['street' => 'Rua das Acácias', 'number' => '321', 'neighborhood' => 'Jardim América', 'complement' => null],
            ['street' => 'Travessa da Paz', 'number' => '55', 'neighborhood' => 'Bela Vista', 'complement' => 'Bloco C, Apto 12'],
        ];

        foreach ($addresses as $index => $address) {
            Property::firstOrCreate(
                ['user_id' => $user->id, 'street' => $address['street'], 'number' => $address['number']],
                [
                    ...$address,
                    'user_id' => $user->id,
                    'person_id' => $people[$index % $people->count()]->id,
                ]
            );
        }
    }
}
