<?php

namespace Tests\Feature;

use App\Mail\AccountDeactivatedMail;
use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\PlatformSettingsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionalEmailTest extends TestCase
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
        ], $overrides);
    }

    public function test_welcome_email_is_sent_on_registration(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        event(new Registered($user));

        Mail::assertSent(WelcomeMail::class, function (WelcomeMail $mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_welcome_email_is_not_sent_when_disabled(): void
    {
        Mail::fake();

        $settings = app(PlatformSettingsService::class);
        $settings->set('transactional_emails_enabled', false);

        event(new Registered(User::factory()->create([
            'email' => 'test@example.com',
        ])));

        Mail::assertNothingSent();
    }

    public function test_password_changed_email_is_sent_from_profile_update(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1'),
        ]);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'OldPassword1',
                'password' => 'NewPassword1',
                'password_confirmation' => 'NewPassword1',
            ])
            ->assertRedirect();

        Mail::assertSent(PasswordChangedMail::class, function (PasswordChangedMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_password_changed_email_is_sent_on_password_reset(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        event(new PasswordReset($user));

        Mail::assertSent(PasswordChangedMail::class, function (PasswordChangedMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_password_changed_email_is_not_sent_when_disabled(): void
    {
        Mail::fake();

        $settings = app(PlatformSettingsService::class);
        $settings->set('email_password_changed_enabled', false);

        $user = User::factory()->create();

        event(new PasswordReset($user));

        Mail::assertNothingSent();
    }

    public function test_account_deactivated_email_is_sent_when_admin_disables_user(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'is_active' => false,
            ])
            ->assertRedirect();

        Mail::assertSent(AccountDeactivatedMail::class, function (AccountDeactivatedMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_account_deactivated_email_is_not_sent_when_user_stays_active(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'is_admin' => false,
            ])
            ->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_admin_can_update_transactional_email_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->platformSettingsPayload([
                'transactional_emails_enabled' => false,
                'email_welcome_enabled' => false,
                'email_password_changed_enabled' => false,
                'email_account_deactivated_enabled' => false,
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $settings = app(PlatformSettingsService::class);

        $this->assertFalse($settings->getBool('transactional_emails_enabled'));
        $this->assertFalse($settings->getBool('email_welcome_enabled'));
        $this->assertFalse($settings->getBool('email_password_changed_enabled'));
        $this->assertFalse($settings->getBool('email_account_deactivated_enabled'));
    }
}
