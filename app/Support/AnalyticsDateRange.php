<?php

namespace App\Support;

use Carbon\Carbon;
use InvalidArgumentException;

class AnalyticsDateRange
{
    public function __construct(
        public readonly string $preset,
        public readonly string $label,
        public readonly Carbon $startLocal,
        public readonly Carbon $endLocal,
        public readonly ?string $customFrom = null,
        public readonly ?string $customTo = null,
    ) {}

    /** @return array<string, string> */
    public static function presets(): array
    {
        return config('analytics.date_range_presets', []);
    }

    public static function isValidPreset(?string $preset): bool
    {
        return is_string($preset) && array_key_exists($preset, self::presets());
    }

    public static function resolvePreset(?string $preset, ?string $fallback = null): string
    {
        if (self::isValidPreset($preset)) {
            return $preset;
        }

        if (self::isValidPreset($fallback)) {
            return $fallback;
        }

        return config('analytics.default_date_range', 'last_30_days');
    }

    public static function fromLegacyRange(int $range): ?string
    {
        $map = config('analytics.legacy_range_map', []);

        return $map[$range] ?? null;
    }

    public static function make(
        string $preset,
        string $timezone,
        ?string $customFrom = null,
        ?string $customTo = null,
    ): self {
        $timezone = TimezoneList::resolve($timezone);
        $now = Carbon::now($timezone);
        $labels = self::presets();

        if (! array_key_exists($preset, $labels)) {
            throw new InvalidArgumentException("Unknown date range preset [{$preset}].");
        }

        [$start, $end] = match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'last_24_hours' => [$now->copy()->subHours(24), $now->copy()],
            'this_week' => [$now->copy()->startOfWeek(Carbon::MONDAY), $now->copy()->endOfDay()],
            'last_7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
            'last_30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'last_90_days' => [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfDay()],
            'last_6_months' => [$now->copy()->subMonths(6)->startOfDay(), $now->copy()->endOfDay()],
            'last_12_months' => [$now->copy()->subMonths(12)->startOfDay(), $now->copy()->endOfDay()],
            'custom' => self::resolveCustomBounds($customFrom, $customTo, $timezone),
            default => throw new InvalidArgumentException("Unhandled preset [{$preset}]."),
        };

        return new self(
            preset: $preset,
            label: self::displayLabel($preset, $labels[$preset], $start, $end, $customFrom, $customTo),
            startLocal: $start,
            endLocal: $end,
            customFrom: $preset === 'custom' ? $start->toDateString() : null,
            customTo: $preset === 'custom' ? $end->toDateString() : null,
        );
    }

    public function startUtc(): Carbon
    {
        return $this->startLocal->copy()->utc();
    }

    public function endUtc(): Carbon
    {
        return $this->endLocal->copy()->utc();
    }

    public function isHourlyTrend(): bool
    {
        return in_array($this->preset, ['today', 'last_24_hours'], true);
    }

    public function dayCount(): int
    {
        if ($this->isHourlyTrend()) {
            return 1;
        }

        return max(1, $this->startLocal->copy()->startOfDay()->diffInDays($this->endLocal->copy()->startOfDay()) + 1);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public function previousPeriodUtc(): array
    {
        $durationSeconds = max(1, $this->endUtc()->diffInSeconds($this->startUtc()));
        $previousEnd = $this->startUtc()->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subSeconds($durationSeconds);

        return [$previousStart, $previousEnd];
    }

    /** @return array<string, string|null> */
    public function toQueryParams(): array
    {
        $params = ['preset' => $this->preset];

        if ($this->preset === 'custom') {
            $params['from'] = $this->customFrom;
            $params['to'] = $this->customTo;
        }

        return $params;
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private static function resolveCustomBounds(?string $from, ?string $to, string $timezone): array
    {
        if ($from === null || $to === null) {
            throw new InvalidArgumentException('Custom date range requires from and to dates.');
        }

        $start = Carbon::parse($from, $timezone)->startOfDay();
        $end = Carbon::parse($to, $timezone)->endOfDay();

        if ($start->gt($end)) {
            throw new InvalidArgumentException('Custom date range start must be before end.');
        }

        $maxDays = (int) config('analytics.max_custom_range_days', 365);

        if ($start->diffInDays($end) + 1 > $maxDays) {
            throw new InvalidArgumentException("Custom date range cannot exceed {$maxDays} days.");
        }

        if ($end->gt(Carbon::now($timezone)->endOfDay())) {
            throw new InvalidArgumentException('Custom date range cannot extend into the future.');
        }

        return [$start, $end];
    }

    private static function displayLabel(
        string $preset,
        string $defaultLabel,
        Carbon $start,
        Carbon $end,
        ?string $customFrom,
        ?string $customTo,
    ): string {
        if ($preset !== 'custom') {
            return $defaultLabel;
        }

        return sprintf('%s – %s', $customFrom ?? $start->toDateString(), $customTo ?? $end->toDateString());
    }
}
