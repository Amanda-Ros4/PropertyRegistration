<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class BirthDate
{
    /**
     * Normaliza DD/MM/YYYY ou YYYY-MM-DD para ISO (Y-m-d).
     */
    public static function toIso(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $raw, $matches) === 1) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

            if (! checkdate($month, $day, $year)) {
                return $raw;
            }

            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $matches) === 1) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            if (! checkdate($month, $day, $year)) {
                return $raw;
            }

            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        try {
            return Carbon::parse($raw)->toDateString();
        } catch (InvalidFormatException) {
            return $raw;
        }
    }
}
