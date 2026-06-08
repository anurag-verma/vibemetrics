<?php

return [

    'retention_days' => (int) env('ANALYTICS_RETENTION_DAYS', 365),

    'rollup_enabled' => env('ANALYTICS_ROLLUP_ENABLED', true),

    'allowed_ranges' => [7, 30, 90],

];
