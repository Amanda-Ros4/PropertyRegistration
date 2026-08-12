<?php

namespace App\Http\Requests\Users;

use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends UserFormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user !== null && ($this->user()?->can('update', $user) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->replace($this->except(['email', 'cpf']));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->route('user');

        return [
            'id' => $this->idRules(),
            'name' => $this->nameRules(),
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'profile' => $this->profileRules(),
            'active' => $this->activeRules(),
        ];
    }
}
