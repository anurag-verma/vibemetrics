<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CollectPageViewRequest;
use App\Jobs\RecordPageView;
use App\Models\Site;
use App\Services\BotDetector;
use App\Services\GeoIpResolver;
use App\Services\PlatformSettingsService;
use App\Services\SiteDomainMatcher;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CollectController extends Controller
{
    public function store(
        CollectPageViewRequest $request,
        BotDetector $botDetector,
        GeoIpResolver $geoIp,
        PlatformSettingsService $settings,
        SiteDomainMatcher $domainMatcher,
    ): Response {
        if ($settings->getBool('maintenance_mode')) {
            abort(503);
        }

        if ($botDetector->isBot($request->userAgent())) {
            return response()->noContent();
        }

        $trackingId = $request->validated('tracking_id');
        $pageUrl = $request->validated('url');

        /** @var Site|null $site */
        $site = Cache::remember(
            "site:{$trackingId}",
            300,
            fn () => Site::query()
                ->where('tracking_id', $trackingId)
                ->first(['id', 'tracking_id', 'domain', 'is_paused'])
        );

        if ($site === null || $site->is_paused) {
            abort(404);
        }

        if (config('analytics.enforce_collect_domain')
            && ! $domainMatcher->matches($site->domain, $pageUrl)) {
            return response()->noContent();
        }

        RecordPageView::dispatch(
            siteId: $site->id,
            payload: $request->validated(),
            country: $geoIp->resolve($request),
            userAgent: $request->userAgent(),
        );

        return response()->noContent();
    }
}
