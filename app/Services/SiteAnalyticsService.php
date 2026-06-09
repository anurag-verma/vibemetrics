<?php

namespace App\Services;

use App\Models\DailyStat;
use App\Models\Site;
use App\Support\AnalyticsDateRange;
use App\Support\AnalyticsSql;
use App\Support\TimezoneList;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SiteAnalyticsService
{
    public function aggregate(Site $site, AnalyticsDateRange $range, ?string $timezone = null): array
    {
        $timezone = TimezoneList::resolve($timezone);

        $cacheKey = sprintf(
            'site_analytics:%d:%s:%s:%s:%s',
            $site->id,
            $range->preset,
            $range->customFrom ?? '',
            $range->customTo ?? '',
            $timezone,
        );

        $metrics = Cache::remember(
            $cacheKey,
            30,
            fn () => $this->buildAggregate($site, $range, $timezone),
        );

        $metrics['live_visitors'] = $this->liveVisitors($site->id);
        $metrics['last_event_at'] = DB::table('page_views')
            ->where('site_id', $site->id)
            ->max('created_at');

        return $metrics;
    }

    /** @return array<string, mixed> */
    private function buildAggregate(Site $site, AnalyticsDateRange $range, string $timezone): array
    {
        $startUtc = $range->startUtc();
        $endUtc = $range->endUtc();
        $startLocal = $range->startLocal->copy();
        $endLocal = $range->endLocal->copy();
        $yesterday = Carbon::now($timezone)->copy()->subDay()->toDateString();
        $today = Carbon::now($timezone)->toDateString();

        $rollups = DailyStat::query()
            ->where('site_id', $site->id)
            ->where('date', '>=', $startLocal->toDateString())
            ->where('date', '<', $today)
            ->orderBy('date')
            ->get();

        if ($range->isHourlyTrend()) {
            $pageViewTrend = $this->pageViewCountsByHour($site->id, $startUtc, $endUtc, $timezone);
            $visitorTrend = $this->uniqueVisitorCountsByHour($site->id, $startUtc, $endUtc, $timezone);
        } else {
            $pageViewCounts = $this->pageViewCountsByLocalDate($site->id, $startLocal, $endLocal, $timezone);
            $visitorCounts = $this->uniqueVisitorCountsByLocalDate($site->id, $startLocal, $endLocal, $timezone);
            $pageViewTrend = $this->buildDailyTrendFromCounts($startLocal, $endLocal, $pageViewCounts);
            $visitorTrend = $this->buildDailyTrendFromCounts($startLocal, $endLocal, $visitorCounts);
        }

        $totalPageViews = $this->countPageViewsInRange($site->id, $startUtc, $endUtc);
        $uniqueVisitors = $this->countUniqueVisitorsInRange($site->id, $startUtc, $endUtc);

        $pageViewsToday = $this->countPageViewsOnLocalDate($site->id, $today, $timezone);
        $pageViewsYesterday = $this->countPageViewsOnLocalDate($site->id, $yesterday, $timezone);

        [$prevStartUtc, $prevEndUtc] = $range->previousPeriodUtc();
        $prevPageViews = $this->countPageViewsInRange($site->id, $prevStartUtc, $prevEndUtc);
        $prevUniqueVisitors = $this->countUniqueVisitorsInRange($site->id, $prevStartUtc, $prevEndUtc);

        $dayCount = $range->dayCount();

        return [
            'date_range' => [
                'preset' => $range->preset,
                'label' => $range->label,
                'from' => $range->customFrom ?? $startLocal->toDateString(),
                'to' => $range->customTo ?? $endLocal->toDateString(),
                'granularity' => $range->isHourlyTrend() ? 'hour' : 'day',
            ],
            'timezone' => $timezone,
            'total_page_views' => $totalPageViews,
            'unique_visitors' => $uniqueVisitors,
            'page_views_today' => $pageViewsToday,
            'page_views_yesterday' => $pageViewsYesterday,
            'views_period_change_pct' => $this->percentChange($totalPageViews, $prevPageViews),
            'visitors_period_change_pct' => $this->percentChange($uniqueVisitors, $prevUniqueVisitors),
            'views_today_change_pct' => $this->percentChange($pageViewsToday, $pageViewsYesterday),
            'comparison_label' => 'vs prior period',
            'live_visitors' => 0,
            'last_event_at' => null,
            'pages_per_visitor' => $uniqueVisitors > 0
                ? round($totalPageViews / $uniqueVisitors, 1)
                : 0,
            'avg_views_per_day' => $dayCount > 0 ? round($totalPageViews / $dayCount, 1) : 0,
            'daily_trend' => $pageViewTrend,
            'visitors_trend' => $visitorTrend,
            'traffic_heatmap' => $this->trafficHeatmap($site->id, $startUtc, $endUtc, $timezone),
            'top_urls' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_urls'),
                $this->topField($site->id, 'url', $startUtc, $endUtc, 10),
                10
            ),
            'top_referrers' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_referrers'),
                $this->topField($site->id, 'referrer', $startUtc, $endUtc, 10, true),
                10
            ),
            'top_campaigns' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_campaigns'),
                $this->topCampaigns($site->id, $startUtc, $endUtc, 10),
                10
            ),
            'devices' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'devices'),
                $this->deviceBreakdown($site->id, $startUtc, $endUtc),
                3
            ),
            'countries' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'countries'),
                $this->topField($site->id, 'country', $startUtc, $endUtc, 10),
                10
            ),
            'browsers' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_browsers'),
                $this->topField($site->id, 'browser', $startUtc, $endUtc, 10, true),
                10
            ),
            'operating_systems' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'top_os'),
                $this->topField($site->id, 'os', $startUtc, $endUtc, 10, true),
                10
            ),
            'utm_sources' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'utm_sources'),
                $this->topUtmField($site->id, 'utm_source', $startUtc, $endUtc, 10),
                10
            ),
            'utm_mediums' => $this->mergeRankings(
                $this->aggregateJsonField($rollups, 'utm_mediums'),
                $this->topUtmField($site->id, 'utm_medium', $startUtc, $endUtc, 10),
                10
            ),
            'channels' => $this->channelBreakdown($site->id, $startUtc, $endUtc),
        ];
    }

    public function liveVisitors(int $siteId): int
    {
        return (int) DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::visitorFingerprintExpression().') as aggregate')
            ->value('aggregate');
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
     * @return array<string, int>
     */
    private function pageViewCountsByLocalDate(int $siteId, Carbon $startDate, Carbon $endDate, string $timezone): array
    {
        $counts = $this->emptyDateCounts($startDate, $endDate);

        $rows = DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $startDate->copy()->utc())
            ->where('created_at', '<=', $endDate->copy()->utc())
            ->pluck('created_at');

        foreach ($rows as $createdAt) {
            $localDate = Carbon::parse($createdAt, 'UTC')->timezone($timezone)->toDateString();

            if (isset($counts[$localDate])) {
                $counts[$localDate]++;
            }
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    private function uniqueVisitorCountsByLocalDate(int $siteId, Carbon $startDate, Carbon $endDate, string $timezone): array
    {
        $counts = $this->emptyDateCounts($startDate, $endDate);

        $rows = DB::table('page_views')
            ->select('created_at', 'visitor_id', 'browser', 'os', 'device')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $startDate->copy()->utc())
            ->where('created_at', '<=', $endDate->copy()->utc())
            ->get();

        $seen = [];

        foreach ($rows as $row) {
            $localDate = Carbon::parse($row->created_at, 'UTC')->timezone($timezone)->toDateString();

            if (! isset($counts[$localDate])) {
                continue;
            }

            $fingerprint = $row->visitor_id
                ?: implode('|', [$row->browser ?? '', $row->os ?? '', $row->device ?? '']);
            $key = "{$localDate}|{$fingerprint}";

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $counts[$localDate]++;
            }
        }

        return $counts;
    }

    /**
     * @return list<array{date: string, count: int}>
     */
    private function pageViewCountsByHour(int $siteId, Carbon $startUtc, Carbon $endUtc, string $timezone): array
    {
        $counts = $this->emptyHourCounts($startUtc, $endUtc, $timezone);

        $rows = DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $startUtc)
            ->where('created_at', '<=', $endUtc)
            ->pluck('created_at');

        foreach ($rows as $createdAt) {
            $hourKey = Carbon::parse($createdAt, 'UTC')->timezone($timezone)->format('Y-m-d H:00');

            if (isset($counts[$hourKey])) {
                $counts[$hourKey]++;
            }
        }

        return collect($counts)
            ->map(fn (int $count, string $date) => ['date' => $date, 'count' => $count])
            ->values()
            ->all();
    }

    /**
     * @return list<array{date: string, count: int}>
     */
    private function uniqueVisitorCountsByHour(int $siteId, Carbon $startUtc, Carbon $endUtc, string $timezone): array
    {
        $counts = $this->emptyHourCounts($startUtc, $endUtc, $timezone);

        $rows = DB::table('page_views')
            ->select('created_at', 'visitor_id', 'browser', 'os', 'device')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $startUtc)
            ->where('created_at', '<=', $endUtc)
            ->get();

        $seen = [];

        foreach ($rows as $row) {
            $hourKey = Carbon::parse($row->created_at, 'UTC')->timezone($timezone)->format('Y-m-d H:00');

            if (! isset($counts[$hourKey])) {
                continue;
            }

            $fingerprint = $row->visitor_id
                ?: implode('|', [$row->browser ?? '', $row->os ?? '', $row->device ?? '']);
            $key = "{$hourKey}|{$fingerprint}";

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $counts[$hourKey]++;
            }
        }

        return collect($counts)
            ->map(fn (int $count, string $date) => ['date' => $date, 'count' => $count])
            ->values()
            ->all();
    }

    /** @return array<string, int> */
    private function emptyDateCounts(Carbon $startDate, Carbon $endDate): array
    {
        $counts = [];
        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $counts[$cursor->toDateString()] = 0;
            $cursor->addDay();
        }

        return $counts;
    }

    /** @return array<string, int> */
    private function emptyHourCounts(Carbon $startUtc, Carbon $endUtc, string $timezone): array
    {
        $counts = [];
        $cursor = $startUtc->copy()->timezone($timezone)->startOfHour();
        $end = $endUtc->copy()->timezone($timezone)->startOfHour();

        while ($cursor->lte($end)) {
            $counts[$cursor->format('Y-m-d H:00')] = 0;
            $cursor->addHour();
        }

        return $counts;
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array{date: string, count: int}>
     */
    private function buildDailyTrendFromCounts(Carbon $startDate, Carbon $endDate, array $counts): array
    {
        $trend = [];
        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $trend[] = ['date' => $date, 'count' => $counts[$date] ?? 0];
            $cursor->addDay();
        }

        return $trend;
    }

    /**
     * @return list<array{day: int, hour: int, count: int}>
     */
    private function trafficHeatmap(int $siteId, Carbon $fromUtc, Carbon $toUtc, string $timezone): array
    {
        $grid = [];

        for ($day = 0; $day < 7; $day++) {
            for ($hour = 0; $hour < 24; $hour++) {
                $grid["{$day}-{$hour}"] = ['day' => $day, 'hour' => $hour, 'count' => 0];
            }
        }

        $rows = DB::table('page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $fromUtc)
            ->where('created_at', '<=', $toUtc)
            ->pluck('created_at');

        foreach ($rows as $createdAt) {
            $local = Carbon::parse($createdAt, 'UTC')->timezone($timezone);
            $dow = (int) $local->format('w');
            $hour = (int) $local->format('G');
            $key = "{$dow}-{$hour}";

            if (isset($grid[$key])) {
                $grid[$key]['count']++;
            }
        }

        return array_values($grid);
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function channelBreakdown(int $siteId, Carbon $from, Carbon $to): array
    {
        $referrers = DB::table('page_views')
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
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
    private function topField(int $siteId, string $field, Carbon $from, Carbon $to, int $limit, bool $skipEmpty = false): array
    {
        $query = DB::table('page_views')
            ->select($field.' as label', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to);

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
    private function topCampaigns(int $siteId, Carbon $from, Carbon $to, int $limit): array
    {
        return $this->topUtmField($siteId, 'utm_campaign', $from, $to, $limit);
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function topUtmField(int $siteId, string $field, Carbon $from, Carbon $to, int $limit): array
    {
        return DB::table('page_views')
            ->select("{$field} as label", DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
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
    private function deviceBreakdown(int $siteId, Carbon $from, Carbon $to): array
    {
        return DB::table('page_views')
            ->select('device as label', DB::raw('COUNT(*) as count'))
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->groupBy('device')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'count' => (int) $row->count])
            ->all();
    }

    private function countPageViewsOnLocalDate(int $siteId, string $date, string $timezone): int
    {
        $start = Carbon::parse($date, $timezone)->startOfDay()->utc();
        $end = Carbon::parse($date, $timezone)->endOfDay()->utc();

        return $this->countPageViewsInRange($siteId, $start, $end);
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
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::visitorFingerprintExpression().') as aggregate')
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
