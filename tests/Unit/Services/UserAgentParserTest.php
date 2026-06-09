<?php

namespace Tests\Unit\Services;

use App\Services\UserAgentParser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserAgentParserTest extends TestCase
{
    private UserAgentParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new UserAgentParser;
    }

    #[DataProvider('browserProvider')]
    public function test_parse_browser(string $userAgent, string $expected): void
    {
        $this->assertSame($expected, $this->parser->parse($userAgent)['browser']);
    }

    #[DataProvider('osProvider')]
    public function test_parse_os(string $userAgent, string $expected): void
    {
        $this->assertSame($expected, $this->parser->parse($userAgent)['os']);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function browserProvider(): array
    {
        return [
            'ios chrome' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1',
                'Chrome',
            ],
            'desktop chrome' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Chrome',
            ],
            'ios safari' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
                'Safari',
            ],
        ];
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function osProvider(): array
    {
        return [
            'iphone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/120.0.6099.119 Mobile/15E148 Safari/604.1',
                'iOS',
            ],
            'macos' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'macOS',
            ],
            'windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Windows',
            ],
        ];
    }
}
