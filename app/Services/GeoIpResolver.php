<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoIpResolver
{
    /** @var list<string> */
    private const HEADER_KEYS = [
        'CF-IPCountry',
        'X-AppEngine-Country',
        'X-Country-Code',
        'CloudFront-Viewer-Country',
    ];

    public function resolve(Request $request): string
    {
        return $this->resolveFromHeaders($request) ?? $this->resolveFromIp($request->ip());
    }

    public function resolveFromHeaders(Request $request): ?string
    {
        if (! config('analytics.trust_geo_headers')) {
            return null;
        }

        foreach (self::HEADER_KEYS as $header) {
            $value = $request->header($header);

            if (is_string($value) && strlen($value) === 2) {
                return strtoupper($value);
            }
        }

        return null;
    }

    public function resolveFromIp(?string $ip): string
    {
        if ($ip === null || $this->isPrivateIp($ip)) {
            return 'XX';
        }

        return Cache::remember(
            "geoip:{$ip}",
            86400,
            fn () => $this->lookupCountry($ip) ?? 'XX'
        );
    }

    private function lookupCountry(string $ip): ?string
    {
        try {
            $response = Http::timeout(2)
                ->get("http://ip-api.com/json/{$ip}", ['fields' => 'countryCode,status']);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'success') {
                return null;
            }

            $code = $data['countryCode'] ?? null;

            return is_string($code) && strlen($code) === 2
                ? strtoupper($code)
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
