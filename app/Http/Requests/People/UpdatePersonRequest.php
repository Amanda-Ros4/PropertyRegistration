<?php

namespace App\Http\Requests\People;

class UpdatePersonRequest extends PersonFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('person')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->preparePersonPayload(includeCpf: false);
    }

    public function rules(): array
    {
        $personId = $this->route('person')?->id;

        return [
            'name' => $this->nameRules(),
            'birth_date' => $this->birthDateRules(),
            'gender' => $this->genderRules(),
            'phone' => $this->phoneRules(),
            'email' => $this->emailRules($personId),
        ];
    }
}
