<?php

namespace App\Http\Middleware;

use App\Services\AnnouncementService;
use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use App\Services\SiteLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        $user = $request->user();
        $siteLimit = app(SiteLimitService::class);
        $settings = app(PlatformSettingsService::class);
        $branding = app(BrandingService::class);
        $announcement = app(AnnouncementService::class);

        return [
            ...parent::share($request),
            'app' => [
                'version' => config('app.version'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'timezone' => $user->preferredTimezone(),
                    'default_date_range' => $user->preferredDateRange(),
                    'email_verified_at' => $user->email_verified_at,
                    'is_admin' => $user->isAdmin(),
                ] : null,
                'sites' => fn () => $user
                    ? Cache::remember("user_sites:{$user->id}", 60, fn () => $user->sites()->select(['id', 'name', 'domain', 'is_paused'])->orderBy('name')->get())
                    : [],
            ],
            'platform' => [
                'maxSitesPerUser' => fn () => $user && ! $siteLimit->isUnlimited($user)
                    ? $siteLimit->maxFor($user)
                    : null,
                'unlimitedSites' => fn () => $user ? $siteLimit->isUnlimited($user) : false,
                'sitesUsed' => fn () => $user ? $siteLimit->used($user) : 0,
                'canAddSite' => fn () => $user ? $siteLimit->canCreate($user) : false,
            ],
            'branding' => fn () => $branding->toArray(),
            'announcement' => fn () => $announcement->forRequest($request),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
