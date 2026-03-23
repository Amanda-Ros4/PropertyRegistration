<?php

namespace Database\Seeders\Concerns;

trait GeneratesValidCpf
{
    /**
     * Gera um CPF válido (11 dígitos) a partir de um seed numérico estável.
     */
    protected function validCpfFromSeed(int $seed): string
    {
        $n = abs($seed * 2654435761 % 1000000000);
        $nine = str_pad((string) $n, 9, '0', STR_PAD_LEFT);
        $digits = array_map(fn (string $d): int => (int) $d, str_split($nine));

        if (count(array_unique($digits)) === 1) {
            $digits[8] = ($digits[8] + 1) % 10;
        }

        return $this->appendCpfCheckDigits($digits);
    }

    /**
     * @param  array<int, int>  $nine
     */
    private function appendCpfCheckDigits(array $nine): string
    {
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $nine[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $d1 = $remainder < 2 ? 0 : 11 - $remainder;

        $ten = array_merge($nine, [$d1]);
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $ten[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $d2 = $remainder < 2 ? 0 : 11 - $remainder;

        return implode('', $nine).$d1.$d2;
    }
}
