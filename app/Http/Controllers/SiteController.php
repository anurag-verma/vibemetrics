<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Models\Site;
use App\Services\SiteLimitService;
use App\Support\DateFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function __construct(
        private SiteLimitService $siteLimit,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $sites = $user->sites()->latest()->get();

        return Inertia::render('Sites/Index', [
            'sites' => $sites->map(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'is_paused' => $site->is_paused,
                'created_at' => DateFormatter::display($site->created_at),
            ]),
            'siteLimit' => $this->siteLimit->maxForDisplay($user),
            'isUnlimitedSites' => $this->siteLimit->isUnlimited($user),
            'sitesUsed' => $this->siteLimit->used($user),
            'canAddSite' => $this->siteLimit->canCreate($user),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! $this->siteLimit->canCreate($request->user())) {
            return redirect()
                ->route('sites.index')
                ->with('error', 'You have reached your site limit.');
        }

        $user = $request->user();

        return Inertia::render('Sites/Create', [
            'siteLimit' => $this->siteLimit->maxForDisplay($user),
            'isUnlimitedSites' => $this->siteLimit->isUnlimited($user),
            'sitesUsed' => $this->siteLimit->used($user),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $user = $request->user();
        $site = $user->sites()->create($request->validated());

        Cache::forget("user_sites:{$user->id}");

        return redirect()
            ->route('sites.edit', $site)
            ->with('success', 'Site created. Copy the tracking snippet to start collecting data.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $this->authorize('delete', $site);

        $userId = $site->user_id;
        $site->delete();

        Cache::forget("user_sites:{$userId}");

        return redirect()->route('sites.index')->with('success', 'Site deleted.');
    }
}
