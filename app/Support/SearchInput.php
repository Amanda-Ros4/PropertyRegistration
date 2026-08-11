<?php

namespace App\Support;

class SearchInput
{
    /**
     * Permite letras, números, espaços e símbolos de sistemas métrico/imperial
     * e de topografia/geometria. Remove demais caracteres especiais.
     */
    public static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace(
            '/[^\p{L}\p{N}\s°′″\'"\/\\\\\-.,:()·×÷±²³µ%‰∠△▲▼◆○●≈≠≤≥]/u',
            '',
            $value
        ) ?? '';

        $clean = preg_replace('/\s+/u', ' ', trim($clean)) ?? '';

        return $clean === '' ? null : $clean;
    }
}
