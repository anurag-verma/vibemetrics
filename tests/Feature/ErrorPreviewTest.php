<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_index_lists_error_pages(): void
    {
        $response = $this->get(route('preview-errors.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dev/ErrorPreviewIndex')
            ->has('statuses', 5)
        );
    }

    public function test_preview_show_renders_inertia_error_page(): void
    {
        $response = $this->get(route('preview-errors.show', ['status' => 404]));

        $response->assertStatus(404);
        $response->assertInertia(fn ($page) => $page
            ->component('Error')
            ->where('status', 404)
        );
    }

    public function test_preview_show_renders_blade_page_for_419(): void
    {
        $response = $this->get(route('preview-errors.show', ['status' => 419]));

        $response->assertStatus(419);
        $response->assertSee('Session expired', false);
    }

    public function test_preview_show_rejects_unknown_status(): void
    {
        $this->get(route('preview-errors.show', ['status' => 418]))->assertNotFound();
    }
}
