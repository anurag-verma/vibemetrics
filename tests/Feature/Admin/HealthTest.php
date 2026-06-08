<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_admin_health_page(): void
    {
        $this->get(route('admin.health.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_admin_health_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.health.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_health_snapshot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get(route('admin.health.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Health/Index')
            ->has('health.status')
            ->has('health.checks.database')
            ->has('health.checks.cache')
            ->has('health.checks.queue')
            ->has('health.checks.storage')
            ->has('health.checks.scheduler')
            ->has('health.tables')
        );
    }
}
