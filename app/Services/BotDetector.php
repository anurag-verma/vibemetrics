<?php

namespace App\Services;

class BotDetector
{
    /** @var list<string> */
    private const PATTERNS = [
        'bot',
        'spider',
        'crawl',
        'slurp',
        'googlebot',
        'bingbot',
        'yandex',
        'baiduspider',
        'duckduckbot',
        'facebookexternalhit',
        'twitterbot',
        'linkedinbot',
        'semrush',
        'ahrefs',
        'mj12bot',
        'dotbot',
        'petalbot',
        'headless',
        'phantomjs',
        'lighthouse',
    ];

    public function isBot(?string $userAgent): bool
    {
        if ($userAgent === null || $userAgent === '') {
            return false;
        }

        $ua = strtolower($userAgent);

        foreach (self::PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
