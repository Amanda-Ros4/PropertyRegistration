<?php

namespace App\Http\Requests\People;

use App\Enums\Gender;
use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('person')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Digits::onlyOrNull($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', new Enum(Gender::class)],
            'phone' => ['nullable', 'string', 'max:11'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('people.fields.name'),
            'birth_date' => __('people.fields.birth_date'),
            'gender' => __('people.fields.gender'),
            'phone' => __('people.fields.phone'),
            'email' => __('people.fields.email'),
        ];
    }
}
