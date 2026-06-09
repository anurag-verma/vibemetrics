<?php

namespace App\Services;

use App\Support\AnalyticsDateRange;
use Illuminate\Http\Request;

class AnalyticsDateRangeResolver
{
    public function resolve(Request $request, string $timezone, ?string $userDefault = null, ?string $platformDefault = null): AnalyticsDateRange
    {
        $fallback = AnalyticsDateRange::resolvePreset(
            $userDefault,
            AnalyticsDateRange::resolvePreset($platformDefault)
        );

        $preset = $request->query('preset');
        $from = $request->query('from');
        $to = $request->query('to');

        if (! AnalyticsDateRange::isValidPreset($preset) && $request->has('range')) {
            $legacy = AnalyticsDateRange::fromLegacyRange((int) $request->query('range'));

            if ($legacy !== null) {
                $preset = $legacy;
            }
        }

        $preset = AnalyticsDateRange::resolvePreset(is_string($preset) ? $preset : null, $fallback);

        try {
            return AnalyticsDateRange::make(
                preset: $preset,
                timezone: $timezone,
                customFrom: is_string($from) ? $from : null,
                customTo: is_string($to) ? $to : null,
            );
        } catch (\InvalidArgumentException) {
            return AnalyticsDateRange::make($fallback, $timezone);
        }
    }
}
