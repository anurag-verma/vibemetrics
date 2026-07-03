<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\AnalyticsDateRangeResolver;
use App\Services\GoalAnalyticsService;
use App\Services\PlatformSettingsService;
use App\Services\SiteAnalyticsService;
use App\Support\AnalyticsDateRange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $site = $request->user()->sites()->latest()->first();

        if ($site === null) {
            return redirect()->route('getting-started');
        }

        return redirect()->route('sites.show', $site);
    }

    public function show(
        Site $site,
        Request $request,
        SiteAnalyticsService $analytics,
        GoalAnalyticsService $goalAnalytics,
        AnalyticsDateRangeResolver $rangeResolver,
    ): Response {
        $this->authorize('view', $site);

        $user = $request->user();
        $platformDefault = $this->platformDefaultDateRange();
        $dateRange = $rangeResolver->resolve(
            $request,
            $user->preferredTimezone(),
            $user->default_date_range,
            $platformDefault,
        );

        $metrics = $analytics->aggregate($site, $dateRange, $user->preferredTimezone());
        $customEvents = $analytics->aggregateCustomEvents($site, $dateRange);
        $goals = $goalAnalytics->forSite($site, $dateRange, $metrics['unique_visitors']);

        return Inertia::render('Dashboard', [
            'site' => $site->only(['id', 'name', 'domain', 'tracking_id', 'is_paused']),
            'metrics' => $metrics,
            'customEvents' => $customEvents,
            'goals' => $goals,
            'dateRange' => $dateRange->toQueryParams() + ['label' => $dateRange->label],
            'dateRangePresets' => collect(AnalyticsDateRange::presets())
                ->except('custom')
                ->all(),
        ]);
    }

    private function platformDefaultDateRange(): string
    {
        $settings = app(PlatformSettingsService::class);
        $value = $settings->get('default_date_range') ?? $settings->get('default_analytics_range');

        if (is_string($value) && AnalyticsDateRange::isValidPreset($value)) {
            return $value;
        }

        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            $legacy = AnalyticsDateRange::fromLegacyRange((int) $value);

            if ($legacy !== null) {
                return $legacy;
            }
        }

        return config('analytics.default_date_range', 'last_30_days');
    }
}
