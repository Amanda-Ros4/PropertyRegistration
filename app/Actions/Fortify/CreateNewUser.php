<?php

namespace App\Actions\Fortify;

use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use App\Models\User;
use App\Rules\ValidCpf;
use App\Support\Digits;
use App\Support\EmailValidation;
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
                'email' => array_merge(EmailValidation::rules(required: true), [Rule::unique('users', 'email')]),
                'password' => $this->passwordRules(),
                'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            ]
        )->validate();

        $user = User::create([
            'name' => $input['name'],
            'cpf' => $cpfDigits,
            'email' => mb_strtolower(trim($input['email'])),
            'password' => Hash::make($input['password']),
            'profile' => UserProfile::Attendant,
            'active' => ActiveStatus::Active,
        ]);

        $user->sendEmailVerificationNotification();

        return $user;
    }
}
