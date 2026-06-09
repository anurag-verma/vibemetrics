<?php

namespace Tests\Unit\Services;

use App\Services\LogReaderService;
use Tests\TestCase;

class LogReaderServiceTest extends TestCase
{
    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('logs/laravel.log');
        @unlink($this->logPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->logPath);

        parent::tearDown();
    }

    public function test_is_allowed_filename_only_permits_laravel_logs(): void
    {
        $service = new LogReaderService;

        $this->assertTrue($service->isAllowedFilename('laravel.log'));
        $this->assertTrue($service->isAllowedFilename('laravel-2026-06-09.log'));
        $this->assertFalse($service->isAllowedFilename('../.env'));
        $this->assertFalse($service->isAllowedFilename('other.log'));
    }

    public function test_read_parses_and_filters_log_entries(): void
    {
        file_put_contents($this->logPath, implode("\n", [
            '[2026-06-09 10:00:00] local.INFO: User logged in',
            '[2026-06-09 10:01:00] local.ERROR: Something failed',
            'Stack trace line',
            '[2026-06-09 10:02:00] local.WARNING: Disk almost full',
        ]));

        $service = new LogReaderService;
        $snapshot = $service->read(
            filename: 'laravel.log',
            level: 'ERROR',
            search: null,
            lines: 500,
        );

        $this->assertSame('laravel.log', $snapshot['file']);
        $this->assertCount(1, $snapshot['entries']);
        $this->assertSame('ERROR', $snapshot['entries'][0]['level']);
        $this->assertStringContainsString('Stack trace line', $snapshot['entries'][0]['message']);
        $this->assertStringContainsString('Something failed', $snapshot['content']);
    }

    public function test_read_applies_search_filter(): void
    {
        file_put_contents($this->logPath, implode("\n", [
            '[2026-06-09 10:00:00] local.INFO: Payment received',
            '[2026-06-09 10:01:00] local.INFO: Page viewed',
        ]));

        $service = new LogReaderService;
        $snapshot = $service->read(
            filename: 'laravel.log',
            level: null,
            search: 'Payment',
            lines: 500,
        );

        $this->assertCount(1, $snapshot['entries']);
        $this->assertStringContainsString('Payment received', $snapshot['content']);
    }
}
