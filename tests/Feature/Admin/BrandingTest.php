<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_branding_text_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'max_sites_per_user' => 2,
                'retention_days' => 365,
                'rollup_enabled' => true,
                'collect_rate_limit' => 120,
                'registration_enabled' => true,
                'default_date_range' => 'last_30_days',
                'maintenance_mode' => false,
                'app_display_name' => 'Honestat',
                'support_email' => 'hello@honestat.com',
                'brand_primary_color' => '#112233',
                'email_logo_same_as_site' => true,
                'transactional_emails_enabled' => true,
                'email_welcome_enabled' => true,
                'email_password_changed_enabled' => true,
                'email_account_deactivated_enabled' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $settings = app(PlatformSettingsService::class);

        $this->assertSame('Honestat', $settings->get('app_display_name'));
        $this->assertSame('hello@honestat.com', $settings->get('support_email'));
        $this->assertSame('#112233', $settings->get('brand_primary_color'));
    }

    public function test_admin_can_upload_and_remove_site_logo(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $file = UploadedFile::fake()->create('logo.png', 10, 'image/png');

        $this->actingAs($admin)
            ->post(route('admin.settings.branding.upload', BrandingService::ASSET_SITE_LOGO), [
                'file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $branding = app(BrandingService::class);

        $this->assertTrue($branding->toArray()['hasCustomSiteLogo']);
        $this->assertStringContainsString('/storage/branding/', $branding->siteLogoUrl());

        $this->actingAs($admin)
            ->delete(route('admin.settings.branding.delete', BrandingService::ASSET_SITE_LOGO))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse(app(BrandingService::class)->toArray()['hasCustomSiteLogo']);
    }

    public function test_non_admin_cannot_upload_branding_assets(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => false]);
        $file = UploadedFile::fake()->create('logo.png', 10, 'image/png');

        $this->actingAs($user)
            ->post(route('admin.settings.branding.upload', BrandingService::ASSET_SITE_LOGO), [
                'file' => $file,
            ])
            ->assertForbidden();
    }
}
