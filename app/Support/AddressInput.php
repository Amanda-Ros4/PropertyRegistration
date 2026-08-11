<?php

namespace App\Support;

class AddressInput
{
    /**
     * Permite letras, números, espaços e símbolos métricos/imperiais/topográficos.
     * Remove especiais como @ # $ %.
     */
    public static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace(
            '/[^\p{L}\p{N}\s°ºª′″\'"\/\\\\\-.,:()·×÷±²³µ∠△▲▼◆○●≈≠≤≥]/u',
            '',
            $value
        ) ?? '';

        $clean = preg_replace('/\s+/u', ' ', trim($clean)) ?? '';

        return $clean === '' ? null : $clean;
    }
}
