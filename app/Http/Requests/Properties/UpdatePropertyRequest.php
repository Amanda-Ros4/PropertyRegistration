<?php

namespace App\Http\Requests\Properties;

use App\Support\AddressInput;
use App\Support\Digits;

class UpdatePropertyRequest extends PropertyFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('property')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $cep = Digits::only($this->input('cep'));
        $number = Digits::only($this->input('number'));

        $this->merge([
            'cep' => $cep === '' ? null : $cep,
            'number' => $number === '' ? null : $number,
            'street' => AddressInput::sanitize($this->input('street')),
            'neighborhood' => AddressInput::sanitize($this->input('neighborhood')),
            'complement' => AddressInput::sanitize($this->input('complement')),
        ]);
    }

    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['land_area'], $rules['building_area']);

        return $rules;
    }
}
