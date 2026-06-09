<?php

namespace Tests\Unit\Support;

use App\Support\AnalyticsDateRange;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AnalyticsDateRangeTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[DataProvider('presetProvider')]
    public function test_presets_resolve_expected_labels(string $preset, string $expectedLabel): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09 15:30:00', 'Asia/Kolkata'));

        $range = AnalyticsDateRange::make($preset, 'Asia/Kolkata');

        $this->assertSame($preset, $range->preset);
        $this->assertSame($expectedLabel, $range->label);
    }

    public function test_custom_range_uses_selected_dates(): void
    {
        $range = AnalyticsDateRange::make('custom', 'UTC', '2026-06-01', '2026-06-07');

        $this->assertSame('custom', $range->preset);
        $this->assertSame('2026-06-01', $range->customFrom);
        $this->assertSame('2026-06-07', $range->customTo);
        $this->assertSame('2026-06-01 – 2026-06-07', $range->label);
    }

    public function test_legacy_range_maps_to_preset(): void
    {
        $this->assertSame('last_30_days', AnalyticsDateRange::fromLegacyRange(30));
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function presetProvider(): array
    {
        return [
            'today' => ['today', 'Today'],
            'last 24 hours' => ['last_24_hours', 'Last 24 hours'],
            'last 30 days' => ['last_30_days', 'Last 30 days'],
            'this year' => ['this_year', 'This year'],
        ];
    }
}
