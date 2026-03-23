<?php

namespace App\Http\Requests\Properties;

use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $cep = Digits::only($this->input('cep'));

        $this->merge([
            'cep' => $cep === '' ? null : $cep,
        ]);
    }

    public function rules(): array
    {
        return [
            'person_id' => [
                'required',
                'integer',
                Rule::exists('people', 'id')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at'),
            ],
            'cep' => ['nullable', 'string', 'size:8', 'regex:/^[0-9]{8}$/'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'complement' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'person_id' => __('properties.fields.owner'),
            'cep' => __('properties.fields.cep'),
            'street' => __('properties.fields.street'),
            'number' => __('properties.fields.number'),
            'neighborhood' => __('properties.fields.neighborhood'),
            'complement' => __('properties.fields.complement'),
        ];
    }
}
