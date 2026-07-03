<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CollectEventRequest;
use App\Jobs\RecordCustomEvent;
use App\Models\Site;
use App\Services\PlatformSettingsService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CollectEventController extends Controller
{
    public function store(
        CollectEventRequest $request,
        PlatformSettingsService $settings,
    ): Response {
        if ($settings->getBool('maintenance_mode')) {
            abort(503);
        }

        $trackingId = $request->validated('tracking_id');

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

        $visitorId = $request->validated('visitor_id');
        if (! is_string($visitorId) || strlen($visitorId) > 36) {
            $visitorId = null;
        }

        RecordCustomEvent::dispatch(
            siteId: $site->id,
            name: $request->validated('name'),
            url: $request->validated('url'),
            visitorId: $visitorId,
            properties: $request->validated('props'),
        );

        return response()->noContent();
    }
}
