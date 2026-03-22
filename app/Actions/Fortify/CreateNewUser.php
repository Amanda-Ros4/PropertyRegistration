<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\ValidCpf;
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
        $cpfFormatted = $this->formatCpf($input['cpf'] ?? '');

        Validator::make(
            array_merge($input, ['cpf' => $cpfFormatted]),
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
            'cpf' => $cpfFormatted,
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }

    private function formatCpf(string $value): string
    {
        $digits = preg_replace('/[^0-9]/', '', $value);

        if (strlen($digits) !== 11) {
            return '';
        }

        return substr($digits, 0, 3).'.'.
            substr($digits, 3, 3).'.'.
            substr($digits, 6, 3).'-'.
            substr($digits, 9, 2);
    }
}
