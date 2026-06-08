<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_creation_rejects_invalid_domain(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/sites/create')
            ->post('/sites', [
                'name' => 'My Site',
                'domain' => 'not a domain',
            ]);

        $response->assertSessionHasErrors('domain');
    }

    public function test_site_creation_rejects_invalid_site_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/sites/create')
            ->post('/sites', [
                'name' => '<script>alert(1)</script>',
                'domain' => 'example.com',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_site_creation_accepts_valid_domain(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/sites', [
                'name' => 'My Blog',
                'domain' => 'example.com',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }
}
