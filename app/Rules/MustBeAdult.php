<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class MustBeAdult implements ValidationRule
{
    public function __construct(private readonly int $minAge = 18) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $birthDate = Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        $minimumBirthDate = now()->subYears($this->minAge)->startOfDay();

        if ($birthDate->greaterThan($minimumBirthDate)) {
            $fail('validation.must_be_adult')->translate(['age' => $this->minAge]);
        }
    }
}
