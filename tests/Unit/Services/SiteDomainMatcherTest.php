<?php

namespace Tests\Unit\Services;

use App\Services\SiteDomainMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteDomainMatcherTest extends TestCase
{
    private SiteDomainMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new SiteDomainMatcher;
    }

    #[DataProvider('matchingUrlsProvider')]
    public function test_matches_registered_domain_and_subdomains(string $domain, string $url): void
    {
        $this->assertTrue($this->matcher->matches($domain, $url));
    }

    public static function matchingUrlsProvider(): array
    {
        return [
            'exact host' => ['example.com', 'https://example.com/about'],
            'www host' => ['example.com', 'https://www.example.com/'],
            'subdomain' => ['example.com', 'https://blog.example.com/post'],
            'with path and query' => ['shop.test', 'https://shop.test/products?id=1'],
        ];
    }

    #[DataProvider('nonMatchingUrlsProvider')]
    public function test_rejects_unrelated_hosts(string $domain, string $url): void
    {
        $this->assertFalse($this->matcher->matches($domain, $url));
    }

    public static function nonMatchingUrlsProvider(): array
    {
        return [
            'different domain' => ['example.com', 'https://evil.com/page'],
            'suffix attack' => ['ample.com', 'https://example.com/page'],
            'invalid url' => ['example.com', 'not-a-url'],
        ];
    }
}
