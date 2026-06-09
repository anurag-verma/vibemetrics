<?php

namespace Tests\Unit\Support;

use App\Support\ServerMetrics;
use Tests\TestCase;

class ServerMetricsTest extends TestCase
{
    public function test_os_info_includes_family_and_label(): void
    {
        $metrics = new ServerMetrics;
        $info = $metrics->osInfo();

        $this->assertArrayHasKey('family', $info);
        $this->assertArrayHasKey('label', $info);
        $this->assertNotEmpty($info['family']);
        $this->assertNotEmpty($info['label']);
    }

    public function test_snapshot_returns_expected_keys(): void
    {
        $metrics = new ServerMetrics;
        $snapshot = $metrics->snapshot();

        $this->assertArrayHasKey('cpu_percent', $snapshot);
        $this->assertArrayHasKey('memory', $snapshot);
        $this->assertArrayHasKey('load_average', $snapshot);
        $this->assertArrayHasKey('uptime_seconds', $snapshot);
        $this->assertArrayHasKey('uptime_human', $snapshot);
    }

    public function test_format_uptime_returns_human_readable_value(): void
    {
        $metrics = new ServerMetrics;

        $this->assertSame('2d 3h', $metrics->formatUptime(183600));
        $this->assertSame('45m', $metrics->formatUptime(2700));
    }
}
