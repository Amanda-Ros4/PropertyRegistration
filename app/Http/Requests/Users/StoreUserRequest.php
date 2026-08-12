<?php

namespace App\Http\Requests\Users;

class StoreUserRequest extends UserFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\User::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareUserPayload();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'cpf' => $this->cpfRules(),
            'password' => $this->passwordRules(),
            'profile' => $this->profileRules(),
            'active' => $this->activeRules(),
        ];
    }
}
