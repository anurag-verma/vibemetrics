<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\PlatformSettingsService;
use App\Services\SiteAnalyticsService;
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

    public function show(Site $site, Request $request, SiteAnalyticsService $analytics, PlatformSettingsService $settings): Response
    {
        $this->authorize('view', $site);

        $defaultRange = $settings->getInt('default_analytics_range', 30);
        $range = (int) $request->query('range', $defaultRange);

        if (! in_array($range, config('analytics.allowed_ranges', [7, 30, 90]), true)) {
            $range = $defaultRange;
        }

        $metrics = $analytics->aggregate($site, $range);

        return Inertia::render('Dashboard', [
            'site' => $site->only(['id', 'name', 'domain', 'tracking_id', 'is_paused']),
            'metrics' => $metrics,
            'range' => $range,
        ]);
    }
}
