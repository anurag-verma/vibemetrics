<?php

namespace App\Http\Controllers;

use App\Services\SiteLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __invoke(Request $request, SiteLimitService $siteLimit): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->sites()->exists()) {
            $site = $user->sites()->latest()->first();

            return redirect()->route('sites.show', $site);
        }

        return Inertia::render('Onboarding/GettingStarted', [
            'canAddSite' => $siteLimit->canCreate($user),
            'siteLimit' => $siteLimit->maxForDisplay($user),
            'isUnlimitedSites' => $siteLimit->isUnlimited($user),
            'sitesUsed' => $siteLimit->used($user),
        ]);
    }
}
