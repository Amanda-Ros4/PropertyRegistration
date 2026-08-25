<?php

namespace App\Support;

class ReportFormatting
{
    public static function area(float|string|null $value): string
    {
        $number = (float) ($value ?? 0);

        if (fmod($number, 1.0) === 0.0) {
            return number_format($number, 0, ',', '.').'m²';
        }

        return number_format($number, 2, ',', '.').'m²';
    }
}
