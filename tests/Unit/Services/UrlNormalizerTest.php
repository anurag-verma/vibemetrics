<?php

namespace Tests\Unit\Services;

use App\Services\UrlNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UrlNormalizerTest extends TestCase
{
    private UrlNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new UrlNormalizer;
    }

    #[DataProvider('urlProvider')]
    public function test_normalize_url(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->normalizer->normalize($input));
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function urlProvider(): array
    {
        return [
            'trailing slash' => [
                'https://example.com/about/',
                'https://example.com/about',
            ],
            'root trailing slash preserved' => [
                'https://example.com/',
                'https://example.com/',
            ],
            'keeps hash' => [
                'https://example.com/page#section',
                'https://example.com/page#section',
            ],
            'root hash path' => [
                'https://example.com/#contact',
                'https://example.com/#contact',
            ],
            'keeps query' => [
                'https://Example.com/Page?ref=1',
                'https://example.com/Page?ref=1',
            ],
        ];
    }
}
