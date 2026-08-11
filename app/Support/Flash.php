<?php

namespace App\Support;

use Illuminate\Support\Str;

class Flash
{
    public static function success(string $message): array
    {
        return self::make('success', $message);
    }

    public static function error(string $message): array
    {
        return self::make('error', $message);
    }

    public static function warn(string $message): array
    {
        return self::make('warn', $message);
    }

    public static function info(string $message): array
    {
        return self::make('info', $message);
    }

    private static function make(string $type, string $message): array
    {
        return [
            'type' => $type,
            'message' => $message,
            'id' => (string) Str::uuid(),
        ];
    }
}
