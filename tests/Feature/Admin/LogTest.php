<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogTest extends TestCase
{
    use RefreshDatabase;

    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logPath = storage_path('logs/laravel.log');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logPath)) {
            @unlink($this->logPath);
        }

        parent::tearDown();
    }

    public function test_guests_cannot_access_admin_logs_page(): void
    {
        $this->get(route('admin.logs.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_access_admin_logs_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.logs.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_logs_page(): void
    {
        file_put_contents($this->logPath, "[2026-06-09 10:00:00] local.INFO: Health check\n");

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get(route('admin.logs.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Logs/Index')
            ->has('files')
            ->has('levels')
            ->has('filters')
            ->has('log.file')
            ->has('log.entries')
            ->has('log.content')
        );
    }

    public function test_admin_cannot_request_invalid_log_file(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.logs.index', ['file' => '../.env']))
            ->assertInvalid(['file']);
    }
}
