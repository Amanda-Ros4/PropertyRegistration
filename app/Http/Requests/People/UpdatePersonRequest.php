<?php

namespace App\Http\Requests\People;

use App\Enums\Gender;
use App\Rules\ValidCpf;
use App\Support\Digits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => Digits::only($this->input('cpf')),
            'phone' => Digits::onlyOrNull($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        $personId = $this->route('person')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'cpf' => [
                'required',
                'string',
                new ValidCpf,
                Rule::unique('people', 'cpf')
                    ->where('user_id', $this->user()->id)
                    ->whereNull('deleted_at')
                    ->ignore($personId),
            ],
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
            'cpf' => __('people.fields.cpf'),
            'gender' => __('people.fields.gender'),
            'phone' => __('people.fields.phone'),
            'email' => __('people.fields.email'),
        ];
    }
}
