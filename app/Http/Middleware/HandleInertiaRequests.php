<?php

namespace App\Http\Middleware;

use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use App\Services\SiteLimitService;
use Illuminate\Http\Request;
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

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'is_admin' => $user->isAdmin(),
                ] : null,
                'sites' => fn () => $user
                    ? $user->sites()->select(['id', 'name', 'domain', 'is_paused'])->orderBy('name')->get()
                    : [],
            ],
            'platform' => [
                'maxSitesPerUser' => fn () => $user && ! $siteLimit->isUnlimited($user)
                    ? $settings->getInt('max_sites_per_user', 2)
                    : null,
                'unlimitedSites' => fn () => $user ? $siteLimit->isUnlimited($user) : false,
                'sitesUsed' => fn () => $user ? $siteLimit->used($user) : 0,
                'canAddSite' => fn () => $user ? $siteLimit->canCreate($user) : false,
            ],
            'branding' => fn () => $branding->toArray(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
