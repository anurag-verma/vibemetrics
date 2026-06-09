<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createSiteFor(User $user, array $attributes = []): Site
    {
        return Site::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Site',
            'domain' => 'example.com',
        ], $attributes));
    }

    public function test_user_cannot_view_another_users_site_dashboard(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $site = $this->createSiteFor($owner);

        $this->actingAs($intruder)
            ->get(route('sites.show', $site))
            ->assertForbidden();
    }

    public function test_user_cannot_edit_another_users_site(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $site = $this->createSiteFor($owner);

        $this->actingAs($intruder)
            ->get(route('sites.edit', $site))
            ->assertForbidden();
    }

    public function test_user_cannot_update_another_users_site(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $site = $this->createSiteFor($owner);

        $this->actingAs($intruder)
            ->patch(route('sites.update', $site), [
                'name' => 'Hijacked',
                'domain' => 'hijacked.com',
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_export_another_users_site(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $site = $this->createSiteFor($owner);

        $this->actingAs($intruder)
            ->get(route('sites.export', $site))
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_site(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $site = $this->createSiteFor($owner);

        $this->actingAs($intruder)
            ->delete(route('sites.destroy', $site))
            ->assertForbidden();

        $this->assertDatabaseHas('sites', ['id' => $site->id]);
    }
}
