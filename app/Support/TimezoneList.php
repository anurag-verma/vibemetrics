<?php

namespace App\Support;

class TimezoneList
{
    /** @return list<string> */
    public static function identifiers(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    public static function isValid(?string $timezone): bool
    {
        if ($timezone === null || $timezone === '') {
            return false;
        }

        return in_array($timezone, self::identifiers(), true);
    }

    public static function resolve(?string $timezone, string $fallback = 'UTC'): string
    {
        if (self::isValid($timezone)) {
            return $timezone;
        }

        return self::isValid($fallback) ? $fallback : 'UTC';
    }
}
