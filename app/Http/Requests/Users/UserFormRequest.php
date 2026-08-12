<?php

namespace App\Http\Requests\Users;

use App\Actions\Fortify\PasswordValidationRules;
use App\Enums\ActiveStatus;
use App\Enums\UserProfile;
use App\Rules\ValidCpf;
use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

abstract class UserFormRequest extends FormRequest
{
    use PasswordValidationRules;

    protected function prepareUserPayload(): void
    {
        $this->merge([
            'cpf' => Digits::only($this->input('cpf')),
            'email' => mb_strtolower(trim((string) $this->input('email', ''))),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected function idRules(): array
    {
        return ['prohibited'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function emailRules(?int $ignoreUserId = null): array
    {
        $rule = Rule::unique('users', 'email');

        if ($ignoreUserId !== null) {
            $rule->ignore($ignoreUserId);
        }

        return ['required', 'email', 'max:255', $rule];
    }

    /**
     * @return array<int, mixed>
     */
    protected function cpfRules(?int $ignoreUserId = null): array
    {
        $rule = Rule::unique('users', 'cpf');

        if ($ignoreUserId !== null) {
            $rule->ignore($ignoreUserId);
        }

        return ['required', 'string', 'size:11', new ValidCpf, $rule];
    }

    /**
     * @return array<int, mixed>
     */
    protected function profileRules(): array
    {
        return [
            'required',
            new Enum(UserProfile::class),
            Rule::in($this->allowedProfiles()),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function activeRules(): array
    {
        return ['required', new Enum(ActiveStatus::class)];
    }

    /**
     * @return array<int, mixed>
     */
    protected function lockedOnUpdateRules(): array
    {
        return ['prohibited'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function activeOnCreateRules(): array
    {
        return ['prohibited'];
    }

    /**
     * @return array<int, string>
     */
    protected function allowedProfiles(): array
    {
        $user = $this->user();

        if ($user?->isTiAdmin()) {
            return UserProfile::values();
        }

        if ($user?->isSystemAdmin()) {
            return [UserProfile::Attendant->value];
        }

        return [];
    }

    public function attributes(): array
    {
        return [
            'name' => __('users.fields.name'),
            'email' => __('users.fields.email'),
            'password' => __('users.fields.password'),
            'password_confirmation' => __('users.fields.password_confirmation'),
            'cpf' => __('users.fields.cpf'),
            'profile' => __('users.fields.profile'),
            'active' => __('users.fields.active'),
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => __('validation.cpf_taken'),
            'cpf.size' => __('validation.invalid_cpf'),
            'email.unique' => __('validation.email_taken'),
            'email.email' => __('validation.email_invalid'),
            'profile.in' => __('validation.user_profile_not_allowed'),
        ];
    }
}
