<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use App\Services\SiteResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    public function edit(Site $site): Response
    {
        $this->authorize('view', $site);

        return Inertia::render('Sites/Edit', $this->editProps($site));
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->validated());

        Cache::forget("site:{$site->tracking_id}");

        return back()->with('success', 'Site settings saved.');
    }

    public function regenerateTrackingId(Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        Cache::forget("site:{$site->tracking_id}");

        $site->update([
            'tracking_id' => (string) Str::uuid(),
        ]);

        Cache::forget("site:{$site->tracking_id}");

        return back()->with('success', 'Tracking ID regenerated. Update your embed snippet.');
    }

    public function reset(Site $site, SiteResetService $resetService): RedirectResponse
    {
        $this->authorize('update', $site);

        $resetService->reset($site);

        return back()->with('success', 'Website statistics have been reset.');
    }

    /** @return array<string, mixed> */
    private function editProps(Site $site): array
    {
        return [
            'site' => $site->only(['id', 'name', 'domain', 'tracking_id', 'is_paused']),
            'trackingSnippet' => $site->isActive() ? $site->trackingSnippet() : null,
        ];
    }
}
