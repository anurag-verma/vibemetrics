<?php

namespace Tests\Unit\Support;

use App\Support\DateFormatter;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class DateFormatterTest extends TestCase
{
    public function test_display_formats_date_as_dd_mm_yyyy(): void
    {
        $date = Carbon::parse('2026-12-06');

        $this->assertSame('06-12-2026', DateFormatter::display($date));
    }

    public function test_display_date_time_includes_time(): void
    {
        $date = Carbon::parse('2026-12-06 14:30:00');

        $this->assertSame('06-12-2026 14:30', DateFormatter::displayDateTime($date));
    }

    public function test_display_returns_null_for_null_input(): void
    {
        $this->assertNull(DateFormatter::display(null));
    }
}
