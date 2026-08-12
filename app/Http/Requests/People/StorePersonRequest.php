<?php

namespace App\Http\Requests\People;

use App\Models\Person;

class StorePersonRequest extends PersonFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Person::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->preparePersonPayload(includeCpf: true);
    }

    public function rules(): array
    {
        return [
            'name' => $this->nameRules(),
            'birth_date' => $this->birthDateRules(),
            'cpf' => $this->cpfRules(),
            'gender' => $this->genderRules(),
            'phone' => $this->phoneRules(),
            'email' => $this->emailRules(),
        ];
    }
}
