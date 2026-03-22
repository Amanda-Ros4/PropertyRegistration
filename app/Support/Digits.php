<?php

namespace App\Support;

/**
 * Normalização de campos numéricos (CPF, telefone) para persistência sem pontuação.
 */
final class Digits
{
    public static function only(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return preg_replace('/[^0-9]/', '', $value);
    }

    public static function onlyOrNull(?string $value): ?string
    {
        $digits = self::only($value ?? '');

        return $digits === '' ? null : $digits;
    }
}
