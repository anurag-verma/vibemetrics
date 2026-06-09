<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_exceed_default_site_limit(): void
    {
        $user = User::factory()->create();

        Site::query()->create([
            'user_id' => $user->id,
            'name' => 'Site One',
            'domain' => 'one.example.com',
        ]);

        Site::query()->create([
            'user_id' => $user->id,
            'name' => 'Site Two',
            'domain' => 'two.example.com',
        ]);

        $this->actingAs($user)
            ->from(route('sites.create'))
            ->post(route('sites.store'), [
                'name' => 'Site Three',
                'domain' => 'three.example.com',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('sites', ['domain' => 'three.example.com']);
    }

    public function test_admin_is_not_limited_by_site_cap(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Site::query()->create([
            'user_id' => $admin->id,
            'name' => 'Site One',
            'domain' => 'one.example.com',
        ]);

        Site::query()->create([
            'user_id' => $admin->id,
            'name' => 'Site Two',
            'domain' => 'two.example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('sites.store'), [
                'name' => 'Site Three',
                'domain' => 'three.example.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sites', ['domain' => 'three.example.com']);
    }
}
