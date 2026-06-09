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

        try {
            new \DateTimeZone($timezone);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public static function resolve(?string $timezone, string $fallback = 'UTC'): string
    {
        if (self::isValid($timezone)) {
            return (new \DateTimeZone((string) $timezone))->getName();
        }

        if (self::isValid($fallback)) {
            return (new \DateTimeZone($fallback))->getName();
        }

        return 'UTC';
    }
}
