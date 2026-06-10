<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\AnnouncementService;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function platformSettingsPayload(array $overrides = []): array
    {
        return array_merge([
            'max_sites_per_user' => 2,
            'retention_days' => 365,
            'rollup_enabled' => true,
            'collect_rate_limit' => 120,
            'registration_enabled' => true,
            'default_date_range' => 'last_30_days',
            'maintenance_mode' => false,
            'app_display_name' => 'VibeMetrics',
            'support_email' => null,
            'brand_primary_color' => '#4f46e5',
            'email_logo_same_as_site' => true,
            'transactional_emails_enabled' => true,
            'email_welcome_enabled' => true,
            'email_password_changed_enabled' => true,
            'email_account_deactivated_enabled' => true,
            'announcement_enabled' => false,
            'announcement_message' => '',
            'announcement_type' => 'info',
            'announcement_audience' => 'authenticated',
            'announcement_link_url' => null,
            'announcement_link_label' => null,
            'announcement_dismissible' => true,
        ], $overrides);
    }

    public function test_admin_can_enable_announcement_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->platformSettingsPayload([
                'announcement_enabled' => true,
                'announcement_message' => 'Maintenance tonight at 10 PM UTC.',
                'announcement_type' => 'warning',
                'announcement_audience' => 'users',
                'announcement_link_url' => 'https://status.example.com',
                'announcement_link_label' => 'Status page',
                'announcement_dismissible' => true,
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $settings = app(PlatformSettingsService::class);

        $this->assertTrue($settings->getBool('announcement_enabled'));
        $this->assertSame('Maintenance tonight at 10 PM UTC.', $settings->get('announcement_message'));
        $this->assertSame('warning', $settings->get('announcement_type'));
        $this->assertSame('users', $settings->get('announcement_audience'));
    }

    public function test_announcement_is_shown_to_matching_audience(): void
    {
        app(PlatformSettingsService::class)->setMany([
            'announcement_enabled' => true,
            'announcement_message' => 'Welcome back.',
            'announcement_audience' => 'users',
        ]);

        $user = User::factory()->create(['is_admin' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $service = app(AnnouncementService::class);

        $userRequest = Request::create('/');
        $userRequest->setUserResolver(fn () => $user);

        $adminRequest = Request::create('/');
        $adminRequest->setUserResolver(fn () => $admin);

        $userAnnouncement = $service->forRequest($userRequest);
        $adminAnnouncement = $service->forRequest($adminRequest);

        $this->assertNotNull($userAnnouncement);
        $this->assertSame('Welcome back.', $userAnnouncement['message']);
        $this->assertNull($adminAnnouncement);
    }

    public function test_announcement_requires_message_when_enabled(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->platformSettingsPayload([
                'announcement_enabled' => true,
                'announcement_message' => '',
            ]))
            ->assertSessionHasErrors('announcement_message');
    }

    public function test_admin_can_save_formatted_announcement_message(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->platformSettingsPayload([
                'announcement_enabled' => true,
                'announcement_message' => '<p><strong>Maintenance</strong> tonight at <u>10 PM</u>.</p><script>alert(1)</script>',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $message = app(PlatformSettingsService::class)->get('announcement_message');

        $this->assertStringContainsString('<strong>Maintenance</strong>', $message);
        $this->assertStringContainsString('<u>10 PM</u>', $message);
        $this->assertStringNotContainsString('<script', $message);
    }
}
