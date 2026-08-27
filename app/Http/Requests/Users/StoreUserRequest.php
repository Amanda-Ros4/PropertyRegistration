<?php

namespace App\Http\Requests\Users;

use App\Models\User;

class StoreUserRequest extends UserFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
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
            'id' => $this->idRules(),
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'cpf' => $this->cpfRules(),
            'password' => $this->passwordRules(),
            'profile' => $this->profileRules(),
            'active' => $this->activeOnCreateRules(),
        ];
    }
}
