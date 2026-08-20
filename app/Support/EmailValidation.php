<?php

namespace App\Support;

use Illuminate\Validation\Rules\Email;

class EmailValidation
{
    /**
     * Regras RFC estritas; fora de testes também valida registro MX do domínio.
     *
     * @return array<int, mixed>
     */
    public static function rules(bool $required = false): array
    {
        $emailRule = Email::default()->strict();

        if (! app()->runningUnitTests()) {
            $emailRule = $emailRule->validateMxRecord();
        }

        $rules = [$emailRule, 'max:255'];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }
}
