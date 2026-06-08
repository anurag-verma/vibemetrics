<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlatformSettingsRequest;
use App\Http\Requests\Admin\UploadBrandingAssetRequest;
use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(PlatformSettingsService $settings, BrandingService $branding): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings->all(),
            'branding' => $branding->toArray(),
        ]);
    }

    public function update(UpdatePlatformSettingsRequest $request, PlatformSettingsService $settings): RedirectResponse
    {
        $settings->setMany($request->validated());

        return back()->with('success', 'Platform settings saved.');
    }

    public function uploadBranding(
        UploadBrandingAssetRequest $request,
        string $type,
        BrandingService $branding,
    ): RedirectResponse {
        if (! in_array($type, [
            BrandingService::ASSET_SITE_LOGO,
            BrandingService::ASSET_EMAIL_LOGO,
            BrandingService::ASSET_FAVICON,
        ], true)) {
            abort(404);
        }

        $branding->storeUpload($request->file('file'), $type);

        return back()->with('success', 'Branding asset uploaded.');
    }

    public function deleteBranding(string $type, BrandingService $branding): RedirectResponse
    {
        if (! in_array($type, [
            BrandingService::ASSET_SITE_LOGO,
            BrandingService::ASSET_EMAIL_LOGO,
            BrandingService::ASSET_FAVICON,
        ], true)) {
            abort(404);
        }

        $branding->removeAsset($type);

        return back()->with('success', 'Branding asset removed.');
    }
}
