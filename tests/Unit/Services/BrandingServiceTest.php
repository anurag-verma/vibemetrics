<?php

namespace Tests\Unit\Services;

use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_name_falls_back_to_config(): void
    {
        $branding = app(BrandingService::class);

        $this->assertSame(config('app.name'), $branding->displayName());
    }

    public function test_email_logo_uses_site_logo_when_same_as_site_enabled(): void
    {
        app(PlatformSettingsService::class)->setMany([
            'email_logo_same_as_site' => true,
            'site_logo_path' => null,
            'email_logo_path' => null,
        ]);

        $branding = app(BrandingService::class);

        $this->assertSame($branding->siteLogoUrl(), $branding->emailLogoUrl());
    }

    public function test_primary_color_defaults_to_indigo(): void
    {
        $branding = app(BrandingService::class);

        $this->assertSame('#4f46e5', $branding->primaryColor());
    }
}
