<?php

namespace Tests\Feature\Api;

use App\Jobs\RecordPageView;
use App\Models\Site;
use App\Models\User;
use App\Services\PlatformSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CollectTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(Site $site): array
    {
        return [
            'tracking_id' => $site->tracking_id,
            'url' => 'https://example.com/about',
            'referrer' => 'https://google.com',
            'device' => 'desktop',
        ];
    }

    private function createSite(array $attributes = []): Site
    {
        $user = User::factory()->create();

        return Site::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Site',
            'domain' => 'example.com',
        ], $attributes));
    }

    public function test_collect_accepts_valid_page_view(): void
    {
        Queue::fake();

        $site = $this->createSite();

        $this->postJson('/api/collect', $this->validPayload($site))
            ->assertNoContent();

        Queue::assertPushed(RecordPageView::class, function (RecordPageView $job) use ($site) {
            return $job->siteId === $site->id
                && $job->payload['url'] === 'https://example.com/about';
        });
    }

    public function test_collect_rejects_unknown_tracking_id(): void
    {
        Queue::fake();

        $this->postJson('/api/collect', [
            'tracking_id' => '00000000-0000-0000-0000-000000000099',
            'url' => 'https://example.com',
        ])->assertNotFound();

        Queue::assertNothingPushed();
    }

    public function test_collect_rejects_paused_site(): void
    {
        Queue::fake();

        $site = $this->createSite(['is_paused' => true]);

        $this->postJson('/api/collect', $this->validPayload($site))
            ->assertNotFound();

        Queue::assertNothingPushed();
    }

    public function test_collect_returns_service_unavailable_in_maintenance_mode(): void
    {
        Queue::fake();

        app(PlatformSettingsService::class)->set('maintenance_mode', true);

        $site = $this->createSite();

        $this->postJson('/api/collect', $this->validPayload($site))
            ->assertStatus(503);

        Queue::assertNothingPushed();
    }

    public function test_collect_ignores_bot_user_agents(): void
    {
        Queue::fake();

        $site = $this->createSite();

        $this->postJson('/api/collect', $this->validPayload($site), [
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ])->assertNoContent();

        Queue::assertNothingPushed();
    }

    public function test_collect_validates_required_fields(): void
    {
        $this->postJson('/api/collect', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tracking_id', 'url']);
    }

    public function test_collect_respects_platform_rate_limit(): void
    {
        Queue::fake();

        app(PlatformSettingsService::class)->set('collect_rate_limit', 10);

        $site = $this->createSite();
        $payload = $this->validPayload($site);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/collect', $payload)->assertNoContent();
        }

        $this->postJson('/api/collect', $payload)->assertStatus(429);
    }

    public function test_deleted_site_cache_does_not_accept_events(): void
    {
        Queue::fake();

        $site = $this->createSite();
        $trackingId = $site->tracking_id;
        $payload = [
            'tracking_id' => $trackingId,
            'url' => 'https://example.com',
        ];

        Cache::remember("site:{$trackingId}", 300, fn () => $site->only(['id', 'tracking_id', 'domain', 'is_paused']));

        $site->delete();

        $this->postJson('/api/collect', $payload)->assertNotFound();

        Queue::assertNothingPushed();
    }

    public function test_collect_rejects_url_on_unregistered_domain(): void
    {
        Queue::fake();

        $site = $this->createSite(['domain' => 'example.com']);

        $this->postJson('/api/collect', [
            'tracking_id' => $site->tracking_id,
            'url' => 'https://evil.com/page',
        ])->assertNoContent();

        Queue::assertNothingPushed();
    }

    public function test_collect_accepts_subdomain_of_registered_domain(): void
    {
        Queue::fake();

        $site = $this->createSite(['domain' => 'example.com']);

        $this->postJson('/api/collect', [
            'tracking_id' => $site->tracking_id,
            'url' => 'https://blog.example.com/post',
        ])->assertNoContent();

        Queue::assertPushed(RecordPageView::class);
    }
}
