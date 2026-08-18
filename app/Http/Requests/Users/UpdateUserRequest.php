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
        if ($this->user()?->isTiAdmin()) {
            $this->prepareUserPayload(includeCpf: false);
        } else {
            $this->request->remove('email');
        }

        $this->request->remove('cpf');
        $this->request->remove('active');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var \App\Models\User $user */
        $user = $this->route('user');

        $rules = [
            'id' => $this->idRules(),
            'name' => $this->nameRules(),
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'profile' => $this->profileRules(),
            'active' => $this->lockedOnUpdateRules(),
            'cpf' => $this->lockedOnUpdateRules(),
        ];

        if ($this->user()?->isTiAdmin()) {
            $rules['email'] = $this->emailRules($user->id);
        }

        return $rules;
    }
}
