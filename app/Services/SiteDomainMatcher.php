<?php

namespace App\Services;

class SiteDomainMatcher
{
    public function matches(string $siteDomain, string $pageUrl): bool
    {
        $host = parse_url($pageUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);
        $domain = $this->normalizeDomain($siteDomain);

        if ($domain === '') {
            return false;
        }

        if ($host === $domain) {
            return true;
        }

        return str_ends_with($host, '.'.$domain);
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');

        return explode('/', $domain)[0] ?? '';
    }
}
