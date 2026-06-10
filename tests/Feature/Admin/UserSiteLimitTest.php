<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use App\Models\User;
use App\Services\SiteLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSiteLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_per_user_site_limit_override(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['site_limit' => null]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'site_limit' => 10,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame(10, $user->site_limit);
        $this->assertSame(10, app(SiteLimitService::class)->maxFor($user));
    }

    public function test_admin_can_clear_per_user_site_limit_override(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['site_limit' => 10]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'site_limit' => null,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertNull($user->site_limit);
        $this->assertSame(2, app(SiteLimitService::class)->maxFor($user));
    }

    public function test_user_with_override_can_create_more_sites_than_platform_default(): void
    {
        $user = User::factory()->create(['site_limit' => 3]);

        foreach (['one.test', 'two.test'] as $domain) {
            Site::query()->create([
                'user_id' => $user->id,
                'name' => $domain,
                'domain' => $domain,
            ]);
        }

        $siteLimit = app(SiteLimitService::class);

        $this->assertTrue($siteLimit->canCreate($user));

        Site::query()->create([
            'user_id' => $user->id,
            'name' => 'three.test',
            'domain' => 'three.test',
        ]);

        $this->assertFalse($siteLimit->canCreate($user));
    }

    public function test_admins_remain_unlimited_regardless_of_site_limit_column(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'site_limit' => 1,
        ]);

        foreach (range(1, 5) as $index) {
            Site::query()->create([
                'user_id' => $admin->id,
                'name' => "Site {$index}",
                'domain' => "site{$index}.test",
            ]);
        }

        $siteLimit = app(SiteLimitService::class);

        $this->assertTrue($siteLimit->isUnlimited($admin));
        $this->assertTrue($siteLimit->canCreate($admin));
    }
}
