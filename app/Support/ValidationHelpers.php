<?php

namespace App\Support;

class ValidationHelpers
{
    public static function normalizePersonName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', trim($name));

        return $normalized === '' ? '' : $normalized;
    }

    public static function normalizeSiteName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        return trim($name);
    }
}
