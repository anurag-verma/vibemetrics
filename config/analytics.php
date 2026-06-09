<?php

return [

    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 365),

    'rollup_enabled' => env('ANALYTICS_ROLLUP_ENABLED', true),

    'default_date_range' => 'last_30_days',

    'max_custom_range_days' => 365,

    /*
    | When true, country headers (e.g. CF-IPCountry) are trusted. Only enable
    | behind a reverse proxy or CDN you control, with trusted proxies configured.
    */
    'trust_geo_headers' => env('ANALYTICS_TRUST_GEO_HEADERS', false),

    /*
    | Reject collect requests whose page URL host does not match the site domain.
    */
    'enforce_collect_domain' => env('ANALYTICS_ENFORCE_DOMAIN', true),

    /** @var array<string, string> */
    'date_range_presets' => [
        'today' => 'Today',
        'last_24_hours' => 'Last 24 hours',
        'this_week' => 'This week',
        'last_7_days' => 'Last 7 days',
        'this_month' => 'This month',
        'last_30_days' => 'Last 30 days',
        'last_90_days' => 'Last 90 days',
        'this_year' => 'This year',
        'last_6_months' => 'Last 6 months',
        'last_12_months' => 'Last 12 months',
        'custom' => 'Custom range',
    ],

    // Legacy integer ranges mapped to presets.
    'legacy_range_map' => [
        7 => 'last_7_days',
        30 => 'last_30_days',
        90 => 'last_90_days',
    ],

];
