<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            Password::min(8)
                ->letters()      // Pelo menos uma letra
                ->numbers()      // Pelo menos um número
                ->symbols()      // Pelo menos um caractere especial (!@#$%...)
                ->mixedCase()    // Maiúsculas e minúsculas
                ->max(128),      // Limite máximo seguro de 128 caracteres
        ];
    }
}
