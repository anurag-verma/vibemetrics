<?php

namespace App\Services;

use App\Models\DailyStat;
use App\Models\Site;
use App\Support\AnalyticsSql;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SiteAnalyticsService
{
    public function aggregate(Site $site, int $days = 30): array
    {
        $days = in_array($days, config('analytics.allowed_ranges', [7, 30, 90]), true) ? $days : 30;

        $startDate = now()->subDays($days - 1)->startOfDay();
        $yesterday = now()->subDay()->toDateString();

        $rollups = DailyStat::query()
            ->where('site_id', $site->id)
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<=', $yesterday)
            ->orderBy('date')
            ->get();

        $liveStats = $this->queryLiveRange($site->id, max($startDate, now()->startOfDay()));

        $rollupMap = $rollups->keyBy(fn (DailyStat $stat) => $stat->date->toDateString());
        $dailyTrend = $this->buildDailyTrend($startDate, $days, $rollups, $liveStats);
        $visitorsTrend = $this->buildVisitorsTrend($startDate, $days, $rollups, $liveStats);

        $totalPageViews = (int) $rollups->sum('page_views') + ($liveStats['page_views'] ?? 0);
        $uniqueVisitors = $this->countUniqueVisitors($site->id, $startDate) + ($liveStats['unique_visitors'] ?? 0);

        if ($rollups->isNotEmpty()) {
            $uniqueVisitors = max($uniqueVisitors, (int) $rollups->sum('unique_visitors'));
        }

        $pageViewsToday = $liveStats['page_views'] ?? 0;
        $pageViewsYesterday = isset($rollupMap[$yesterday])
            ? (int) $rollupMap[$yesterday]->page_views
            : $this->countPageViewsOnDate($site->id, $yesterday);

        $prevStart = $startDate->copy()->subDays($days);
        $prevEnd = $startDate->copy()->subSecond();
        $prevPageViews = $this->countPageViewsInRange($site->id, $prevStart, $prevEnd);
        $prevUniqueVisitors = $this->countUniqueVisitorsInRange($site->id, $prevStart, $prevEnd);

        $lastEventAt = DB::table('page_views')
            ->where('site_id', $site->id)
            ->max('created_at');

        return [
            'range' => $days,
            'total_page_views' => $totalPageViews,
            'unique_visitors' => $uniqueVisitors,
            'page_views_today' => $pageViewsToday,
            'page_views_yesterday' => $pageViewsYesterday,
            'views_period_change_pct' => $this->percentChange($totalPageViews, $prevPageViews),
            'visitors_period_change_pct' => $this->percentChange($uniqueVisitors, $prevUniqueVisitors),
            'views_today_change_pct' => $this->percentChange($pageViewsToday, $pageViewsYesterday),
            'live_visitors' => $this->liveVisitors($site->id),
            'last_event_at' => $lastEventAt,
            'pages_per_visitor' => $uniqueVisitors > 0
                ? round($totalPageViews / $uniqueVisitors, 1)
                : 0,
            'avg_views_per_day' => $days > 0 ? round($totalPageViews / $days, 1) : 0,
            'daily_trend' => $dailyTrend,
            'visitors_trend' => $visitorsTrend,
            'traffic_heatmap' => $this->trafficHeatmap($site->id, $startDate),
            'top_urls' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_urls'),
                $this->topField($site->id, 'url', $startDate, 10),
                10
            ),
            'top_referrers' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_referrers'),
                $this->topField($site->id, 'referrer', $startDate, 10, true),
                10
            ),
            'top_campaigns' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_campaigns'),
                $this->topCampaigns($site->id, $startDate, 10),
                10
            ),
            'devices' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'devices'),
                $this->deviceBreakdown($site->id, $startDate),
                3
            ),
            'countries' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'countries'),
                $this->topField($site->id, 'country', $startDate, 10),
                10
            ),
            'browsers' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_browsers'),
                $this->topField($site->id, 'browser', $startDate, 10, true),
                10
            ),
            'operating_systems' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_os'),
                $this->topField($site->id, 'os', $startDate, 10, true),
                10
            ),
            'utm_sources' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'utm_sources'),
                $this->topUtmField($site->id, 'utm_source', $startDate, 10),
                10
            ),
            'utm_mediums' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'utm_mediums'),
                $this->topUtmField($site->id, 'utm_medium', $startDate, 10),
                10
            ),
            'channels' => $this->channelBreakdown($site->id, $startDate),
        ];
    }

    public function liveVisitors(int $siteId): int
    {
        return (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();
    }

    /** @return array<int, int> site_id => page view count */
    public function pageViewCountsSince(array $siteIds, Carbon $from, ?Carbon $to = null): array
    {
        if ($siteIds === []) {
            return [];
        }

        $query = DB::table('page_views')
            ->whereIn('site_id', $siteIds)
            ->where('created_at', '>=', $from);

        if ($to !== null) {
            $query->where('created_at', '<=', $to);
        }

        return $query
            ->select('site_id', DB::raw('count(*) as aggregate'))
            ->groupBy('site_id')
            ->pluck('aggregate', 'site_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }

    /**
     * @return array{page_views: int, unique_visitors: int}
     */
    private function queryLiveRange(int $siteId, Carbon $from): array
    {
        if ($from->isFuture()) {
            return ['page_views' => 0, 'unique_visitors' => 0];
        }

        $pageViews = (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->count();

        $uniqueVisitors = (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::dailyVisitorFingerprintExpression().') as aggregate')
            ->value('aggregate');

        return [
            'page_views' => $pageViews,
            'unique_visitors' => $uniqueVisitors,
        ];
    }

    private function countUniqueVisitors(int $siteId, Carbon $from): int
    {
        return (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', now()->startOfDay())
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::dailyVisitorFingerprintExpression().') as aggregate')
            ->value('aggregate');
    }

    /**
     * @param  Collection<int, DailyStat>  $rollups
     * @param  array{page_views: int}  $liveStats
     * @return list<array{date: string, count: int}>
     */
    private function buildDailyTrend(Carbon $startDate, int $days, Collection $rollups, array $liveStats): array
    {
        $trend = [];
        $rollupMap = $rollups->keyBy(fn (DailyStat $stat) => $stat->date->toDateString());

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $count = isset($rollupMap[$date]) ? (int) $rollupMap[$date]->page_views : 0;

            if ($date === now()->toDateString()) {
                $count = $liveStats['page_views'] ?? 0;
            }

            $trend[] = ['date' => $date, 'count' => $count];
        }

        return $trend;
    }

    /**
     * @param  Collection<int, DailyStat>  $rollups
     * @param  array{unique_visitors: int}  $liveStats
     * @return list<array{date: string, count: int}>
     */
    private function buildVisitorsTrend(Carbon $startDate, int $days, Collection $rollups, array $liveStats): array
    {
        $trend = [];
        $rollupMap = $rollups->keyBy(fn (DailyStat $stat) => $stat->date->toDateString());

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();
            $count = isset($rollupMap[$date]) ? (int) $rollupMap[$date]->unique_visitors : 0;

            if ($date === now()->toDateString()) {
                $count = $liveStats['unique_visitors'] ?? 0;
            }

            $trend[] = ['date' => $date, 'count' => $count];
        }

        return $trend;
    }

    /**
     * @return list<array{day: int, hour: int, count: int}>
     */
    private function trafficHeatmap(int $siteId, Carbon $from): array
    {
        $dowExpr = AnalyticsSql::dayOfWeekExpression();
        $hourExpr = AnalyticsSql::hourExpression();

        $rows = DB::table('page_views')
            ->selectRaw("{$dowExpr} as dow, {$hourExpr} as hour, COUNT(*) as count")
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->groupByRaw("{$dowExpr}, {$hourExpr}")
            ->get();

        $grid = [];

        for ($day = 0; $day < 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $grid["{$day}-{$hour}"] = ['day' => $day, 'hour' => $hour, 'count' => 0];
            }
        }

        foreach ($rows as $row) {
            $key = "{$row->dow}-{$row->hour}";

            if (isset($grid[$key])) {
                $grid[$key]['count'] = (int) $row->count;
            }
        }

        return array_values($grid);
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function channelBreakdown(int $siteId, Carbon $from): array
    {
        $referrers = DB::table('page_views')
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->groupBy('referrer')
            ->get();

        $channels = [
            'Direct' => 0,
            'Organic' => 0,
            'Social' => 0,
            'Referral' => 0,
        ];

        foreach ($referrers as $row) {
            $channel = $this->classifyReferrer($row->referrer);
            $channels[$channel] += (int) $row->count;
        }

        arsort($channels);

        return collect($channels)
            ->filter(fn (int $count) => $count > 0)
            ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }

    private function classifyReferrer(?string $referrer): string
    {
        if ($referrer === null || trim($referrer) === '') {
            return 'Direct';
        }

        $host = strtolower(parse_url($referrer, PHP_URL_HOST) ?? $referrer);
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        $organic = ['google.', 'bing.', 'duckduckgo.', 'yahoo.', 'baidu.', 'yandex.', 'ecosia.'];
        foreach ($organic as $needle) {
            if (str_contains($host, $needle)) {
                return 'Organic';
            }
        }

        $social = [
            'twitter.', 'x.com', 't.co', 'facebook.', 'instagram.', 'linkedin.',
            'tiktok.', 'reddit.', 'pinterest.', 'youtube.', 'threads.net',
            'mastodon.', 'bsky.app',
        ];
        foreach ($social as $needle) {
            if (str_contains($host, $needle)) {
                return 'Social';
            }
        }

        return 'Referral';
    }

    /**
     * @param  Collection<int, DailyStat>  $rollups
     * @return list<array{label: string, count: int}>
     */
    private function aggregateJsonField(Collection $rollups, string $field): array
    {
        $totals = [];

        foreach ($rollups as $rollup) {
            $items = $rollup->{$field} ?? [];

            foreach ($items as $item) {
                $label = (string) ($item['label'] ?? '');
                $count = (int) ($item['count'] ?? 0);

                if ($label === '') {
                    continue;
                }

                $totals[$label] = ($totals[$label] ?? 0) + $count;
            }
        }

        arsort($totals);

        return collect($totals)
            ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{label: string, count: int}>  $a
     * @param  list<array{label: string, count: int}>  $b
     * @return list<array{label: string, count: int}>
     */
    private function mergeRankings(array $a, array $b, int $limit): array
    {
        $totals = [];

        foreach (array_merge($a, $b) as $item) {
            $label = $item['label'];
            $totals[$label] = ($totals[$label] ?? 0) + $item['count'];
        }

        arsort($totals);

        return collect($totals)
            ->take($limit)
            ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function topField(int $siteId, string $field, Carbon $from, int $limit, bool $skipEmpty = false): array
    {
        $query = DB::table('page_views')
            ->select($field.' as label', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from);

        if ($skipEmpty) {
            $query->whereNotNull($field)->where($field, '!=', '');
        }

        return $query
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
    private function topCampaigns(int $siteId, Carbon $from, int $limit): array
    {
        return $this->topUtmField($siteId, 'utm_campaign', $from, $limit);
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function topUtmField(int $siteId, string $field, Carbon $from, int $limit): array
    {
        return DB::table('page_views')
            ->select("{$field} as label", DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->whereNotNull($field)
            ->where($field, '!=', '')
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
    private function deviceBreakdown(int $siteId, Carbon $from): array
    {
        return DB::table('page_views')
            ->select('device as label', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->groupBy('device')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->count])
            ->all();
    }

    private function countPageViewsOnDate(int $siteId, string $date): int
    {
        return (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->whereDate('created_at', $date)
            ->count();
    }

    private function countPageViewsInRange(int $siteId, Carbon $from, Carbon $to): int
    {
        return (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->count();
    }

    private function countUniqueVisitorsInRange(int $siteId, Carbon $from, Carbon $to): int
    {
        return (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::dailyVisitorFingerprintExpression().') as aggregate')
            ->value('aggregate');
    }

    private function percentChange(int $current, int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }
}
