<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsDateRangeResolver;
use App\Services\PlatformAnalyticsService;
use App\Services\PlatformSettingsService;
use App\Support\AnalyticsDateRange;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        PlatformAnalyticsService $analytics,
        AnalyticsDateRangeResolver $rangeResolver,
        PlatformSettingsService $settings,
    ): Response {
        $platformDefault = $settings->get('default_date_range') ?? $settings->get('default_analytics_range');
        if (is_int($platformDefault) || (is_string($platformDefault) && ctype_digit($platformDefault))) {
            $platformDefault = AnalyticsDateRange::fromLegacyRange((int) $platformDefault) ?? 'last_30_days';
        }

        $dateRange = $rangeResolver->resolve(
            $request,
            $request->user()->preferredTimezone(),
            $request->user()->preferredDateRange(),
            is_string($platformDefault) ? $platformDefault : 'last_30_days',
        );

        return Inertia::render('Admin/Dashboard', $analytics->overview($dateRange));
    }
}
