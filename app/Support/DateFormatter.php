<?php

namespace App\Support;

use Carbon\CarbonInterface;

class DateFormatter
{
    public static function display(?CarbonInterface $date): ?string
    {
        return $date?->format('d-m-Y');
    }

    public static function displayDateTime(?CarbonInterface $date): ?string
    {
        return $date?->format('d-m-Y H:i');
    }
}
