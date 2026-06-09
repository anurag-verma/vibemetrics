<?php

namespace App\Services;

class UrlNormalizer
{
    public function normalize(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return mb_substr($url, 0, 2048);
        }

        $scheme = strtolower($parts['scheme'] ?? 'https');
        $host = strtolower($parts['host']);
        $path = $parts['path'] ?? '/';

        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/') ?: '/';
        }

        if ($path === '') {
            $path = '/';
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?'.$parts['query'] : '';

        return mb_substr("{$scheme}://{$host}{$path}{$query}", 0, 2048);
    }
}
