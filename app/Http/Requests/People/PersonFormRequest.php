<?php

namespace App\Http\Requests\People;

use App\Enums\Gender;
use App\Rules\MustBeAdult;
use App\Rules\ValidBrazilianMobile;
use App\Rules\ValidCpf;
use App\Support\BirthDate;
use App\Support\Digits;
use App\Support\EmailValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;

abstract class PersonFormRequest extends FormRequest
{
    protected function preparePersonPayload(bool $includeCpf = false): void
    {
        $email = trim((string) $this->input('email', ''));

        $payload = [
            'phone' => Digits::onlyOrNull($this->input('phone')),
            'email' => $email === '' ? null : mb_strtolower($email),
            'birth_date' => BirthDate::toIso($this->input('birth_date')),
        ];

        if ($includeCpf) {
            $payload['cpf'] = Digits::only($this->input('cpf'));
        }

        $this->merge($payload);
    }

    /**
     * @return array<int, mixed>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function birthDateRules(): array
    {
        return ['required', 'date', 'before_or_equal:today', new MustBeAdult];
    }

    /**
     * @return array<int, mixed>
     */
    protected function genderRules(): array
    {
        return ['required', new Enum(Gender::class)];
    }

    /**
     * @return array<int, mixed>
     */
    protected function phoneRules(): array
    {
        return ['nullable', 'string', new ValidBrazilianMobile];
    }

    /**
     * @return array<int, mixed>
     */
    protected function emailRules(?int $ignorePersonId = null): array
    {
        return [
            ...EmailValidation::rules(required: false),
            $this->uniqueForUser('email', $ignorePersonId),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function cpfRules(?int $ignorePersonId = null): array
    {
        return [
            'required',
            'string',
            'size:11',
            new ValidCpf,
            $this->uniqueForUser('cpf', $ignorePersonId),
        ];
    }

    protected function uniqueForUser(string $column, ?int $ignorePersonId = null): Unique
    {
        $ownerId = $this->route('person')?->user_id ?? $this->user()?->id;

        $rule = Rule::unique('people', $column)
            ->where('user_id', $ownerId)
            ->whereNull('deleted_at');

        if ($ignorePersonId !== null) {
            $rule->ignore($ignorePersonId);
        }

        return $rule;
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

    public function messages(): array
    {
        return [
            'cpf.unique' => __('validation.cpf_taken'),
            'cpf.size' => __('validation.invalid_cpf'),
            'email.unique' => __('validation.email_taken'),
            'email.email' => __('validation.email_invalid'),
        ];
    }
}
