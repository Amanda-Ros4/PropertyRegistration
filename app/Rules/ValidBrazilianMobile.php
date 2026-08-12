<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBrazilianMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value) ?? '';

        if (strlen($digits) !== 11) {
            $fail('validation.invalid_phone')->translate();

            return;
        }

        $areaCode = (int) substr($digits, 0, 2);

        if ($areaCode < 11 || $digits[2] !== '9') {
            $fail('validation.invalid_phone')->translate();
        }
    }
}
