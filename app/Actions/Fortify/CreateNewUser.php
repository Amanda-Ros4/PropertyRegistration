<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\ValidCpf;
use App\Support\Digits;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $cpfDigits = Digits::only($input['cpf'] ?? '');

        Validator::make(
            array_merge($input, ['cpf' => $cpfDigits]),
            [
                'name' => ['required', 'string', 'max:255'],
                'cpf' => ['required', 'string', new ValidCpf, Rule::unique('users', 'cpf')],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules(),
                'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            ]
        )->validate();

        return User::create([
            'name' => $input['name'],
            'cpf' => $cpfDigits,
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
