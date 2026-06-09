<?php

namespace Tests\Feature\Analytics;

use App\Models\Site;
use App\Models\User;
use App\Services\SiteAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LiveVisitorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_visitors_counts_distinct_visitors_not_page_views(): void
    {
        $user = User::factory()->create();
        $site = Site::query()->create([
            'user_id' => $user->id,
            'name' => 'Test',
            'domain' => 'example.com',
        ]);

        $visitorId = '11111111-1111-4111-8111-111111111111';

        foreach (range(1, 4) as $i) {
            DB::table('page_views')->insert([
                'site_id' => $site->id,
                'visitor_id' => $visitorId,
                'url' => 'https://example.com/',
                'device' => 'mobile',
                'browser' => 'Chrome',
                'os' => 'iOS',
                'country' => 'IN',
                'created_at' => now(),
            ]);
        }

        DB::table('page_views')->insert([
            'site_id' => $site->id,
            'visitor_id' => '22222222-2222-4222-8222-222222222222',
            'url' => 'https://example.com/',
            'device' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'country' => 'IN',
            'created_at' => now(),
        ]);

        $service = app(SiteAnalyticsService::class);

        $this->assertSame(2, $service->liveVisitors($site->id));
    }
}
