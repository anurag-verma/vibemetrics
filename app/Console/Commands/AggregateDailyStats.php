<?php

namespace App\Console\Commands;

use App\Models\DailyStat;
use App\Models\Site;
use App\Services\PlatformSettingsService;
use App\Support\AnalyticsSql;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateDailyStats extends Command
{
    protected $signature = 'analytics:rollup {--date=}';

    protected $description = 'Aggregate page view statistics into daily_stats table';

    public function handle(PlatformSettingsService $settings): int
    {
        if (! $settings->getBool('rollup_enabled', true)) {
            $this->info('Rollup is disabled.');

            return self::SUCCESS;
        }

        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : now()->subDay()->toDateString();

        $sites = Site::query()->pluck('id');

        foreach ($sites as $siteId) {
            $this->aggregateSite((int) $siteId, $date);
        }

        $settings->set('last_rollup_at', now()->toIso8601String());

        $this->info("Rollup complete for {$date}.");

        return self::SUCCESS;
    }

    private function aggregateSite(int $siteId, string $date): void
    {
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $pageViews = (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        if ($pageViews === 0) {
            return;
        }

        $uniqueVisitors = (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::visitorFingerprintExpression().') as aggregate')
            ->value('aggregate');

        DailyStat::query()->updateOrCreate(
            ['site_id' => $siteId, 'date' => $date],
            [
                'page_views' => $pageViews,
                'unique_visitors' => $uniqueVisitors,
                'devices' => $this->ranked($siteId, 'device', $start, $end),
                'top_browsers' => $this->ranked($siteId, 'browser', $start, $end, 10),
                'top_os' => $this->ranked($siteId, 'os', $start, $end, 10),
                'countries' => $this->ranked($siteId, 'country', $start, $end, 10),
                'top_urls' => $this->ranked($siteId, 'url', $start, $end, 10),
                'top_referrers' => $this->rankedReferrers($siteId, $start, $end),
                'top_campaigns' => $this->rankedCampaigns($siteId, $start, $end),
                'utm_sources' => $this->rankedUtm($siteId, 'utm_source', $start, $end),
                'utm_mediums' => $this->rankedUtm($siteId, 'utm_medium', $start, $end),
            ]
        );
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function ranked(int $siteId, string $field, Carbon $start, Carbon $end, int $limit = 3): array
    {
        return DB::table('page_views')
            ->select("{$field} as label", DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy($field)
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->count])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function rankedReferrers(int $siteId, Carbon $start, Carbon $end): array
    {
        return DB::table('page_views')
            ->select('referrer as label', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->count])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function rankedCampaigns(int $siteId, Carbon $start, Carbon $end): array
    {
        return DB::table('page_views')
            ->select('utm_campaign as label', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('utm_campaign')
            ->where('utm_campaign', '!=', '')
            ->groupBy('utm_campaign')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->count])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function rankedUtm(int $siteId, string $field, Carbon $start, Carbon $end): array
    {
        return DB::table('page_views')
            ->select("{$field} as label", DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull($field)
            ->where($field, '!=', '')
            ->groupBy($field)
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->count])
            ->all();
    }
}
