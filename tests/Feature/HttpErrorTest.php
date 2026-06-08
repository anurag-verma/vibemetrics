<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class HttpErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_route_returns_branded_404_page(): void
    {
        $response = $this->get('/this-route-does-not-exist');

        $response->assertStatus(404);
        $response->assertSee('This page wandered off', false);
        $response->assertSee('404', false);
    }

    public function test_inertia_404_returns_error_page_component(): void
    {
        $response = $this->getInertia('/this-route-does-not-exist');

        $response->assertStatus(404);
        $response->assertJson([
            'component' => 'Error',
            'props' => ['status' => 404],
        ]);
    }

    public function test_token_mismatch_redirects_to_login_with_message(): void
    {
        $request = Request::create('/admin/settings', 'PUT');

        $baseResponse = app(ExceptionHandler::class)->render(
            $request,
            new TokenMismatchException('CSRF token mismatch.'),
        );

        $response = $this->createTestResponse($baseResponse, $request);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Your session has expired. Please sign in again.');
    }

    public function test_admin_route_returns_403_for_non_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/settings');

        $response->assertStatus(403);
        $response->assertSee('Access denied', false);
    }

    public function test_inertia_403_returns_error_page_component(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->withHeaders($this->inertiaHeaders())->get('/admin/settings');

        $response->assertStatus(403);
        $response->assertJson([
            'component' => 'Error',
            'props' => ['status' => 403],
        ]);
    }
}
