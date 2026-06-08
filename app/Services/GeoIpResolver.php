<?php

namespace App\Services;

use Illuminate\Http\Request;

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
        foreach (self::HEADER_KEYS as $header) {
            $value = $request->header($header);

            if (is_string($value) && strlen($value) === 2) {
                return strtoupper($value);
            }
        }

        return 'US';
    }
}
