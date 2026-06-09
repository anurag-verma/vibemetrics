<?php

namespace App\Services;

use App\Models\DailyStat;
use App\Models\Site;
use App\Models\User;
use App\Support\AnalyticsDateRange;
use App\Support\AnalyticsSql;
use App\Support\DateFormatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsService
{
    public function __construct(
        private PlatformSettingsService $settings,
    ) {}

    /** @return array<string, mixed> */
    public function overview(AnalyticsDateRange $range): array
    {
        $pageViewsToday = $this->pageViewsOnDate(today());
        $pageViewsYesterday = $this->pageViewsOnDate(today()->subDay());

        return [
            'dateRange' => $range->toQueryParams() + ['label' => $range->label],
            'kpis' => $this->kpis($pageViewsToday, $pageViewsYesterday),
            'system' => $this->systemStatus(),
            'registrationTrend' => $this->registrationTrend($range),
            'trafficTrend' => $this->trafficTrend($range),
            'ingestionRate' => $this->ingestionRate(),
            'topSites' => $this->topSites($range),
            'recentActivity' => $this->recentActivity(),
        ];
    }

    /** @return array<string, mixed> */
    private function kpis(int $pageViewsToday, int $pageViewsYesterday): array
    {
        $dbGrowth = $pageViewsYesterday > 0
            ? round((($pageViewsToday - $pageViewsYesterday) / $pageViewsYesterday) * 100, 1)
            : ($pageViewsToday > 0 ? 100.0 : 0.0);

        $siteLimit = $this->settings->getInt('max_sites_per_user', 2);

        return [
            'total_users' => User::count(),
            'new_users_7d' => User::where('created_at', '>=', now()->subDays(6)->startOfDay())->count(),
            'disabled_users' => User::where('is_active', false)->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'total_sites' => Site::count(),
            'paused_sites' => Site::where('is_paused', true)->count(),
            'new_sites_7d' => Site::where('created_at', '>=', now()->subDays(6)->startOfDay())->count(),
            'users_at_limit' => User::query()
                ->withCount('sites')
                ->get()
                ->filter(fn (User $user) => $user->sites_count >= $siteLimit)
                ->count(),
            'total_page_views' => (int) DB::table('page_views')->count(),
            'page_views_today' => $pageViewsToday,
            'db_growth' => $dbGrowth,
            'live_events' => $this->liveEvents(),
        ];
    }

    /** @return array<string, mixed> */
    private function systemStatus(): array
    {
        return [
            'maintenance_mode' => $this->settings->getBool('maintenance_mode'),
            'registration_enabled' => $this->settings->getBool('registration_enabled', true),
            'rollup_enabled' => $this->settings->getBool('rollup_enabled', true),
            'retention_days' => $this->settings->getInt('retention_days', 365),
            'collect_rate_limit' => $this->settings->getInt('collect_rate_limit', 120),
            'max_sites_per_user' => $this->settings->getInt('max_sites_per_user', 2),
        ];
    }

    /** @return list<array{date: string, count: int}> */
    private function registrationTrend(AnalyticsDateRange $range): array
    {
        $startDate = $range->startLocal->copy()->startOfDay();
        $endDate = $range->endLocal->copy()->startOfDay();
        $days = $range->dayCount();

        $counts = User::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $range->startUtc())
            ->where('created_at', '<=', $range->endUtc())
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return $this->fillDateRange($startDate, $days, $counts);
    }

    /** @return list<array{date: string, count: int}> */
    private function trafficTrend(AnalyticsDateRange $range): array
    {
        $startDate = $range->startLocal->copy()->startOfDay();
        $days = $range->dayCount();
        $yesterday = now()->subDay()->toDateString();

        $rollupCounts = DailyStat::query()
            ->selectRaw('date, SUM(page_views) as count')
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<=', $yesterday)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        $liveCounts = DB::table('page_views')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $range->startUtc())
            ->where('created_at', '<=', $range->endUtc())
            ->groupBy('date')
            ->pluck('count', 'date');

        $counts = collect($rollupCounts)->merge($liveCounts)->all();

        return $this->fillDateRange($startDate, $days, $counts);
    }

    /** @return list<array{hour: string, count: int}> */
    private function ingestionRate(): array
    {
        $driver = DB::connection()->getDriverName();
        $hourExpr = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d %H:00', created_at)",
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM-DD HH24:00')",
            default => "DATE_FORMAT(created_at, '%Y-%m-%d %H:00')",
        };

        $startHour = now()->subHours(23)->startOfHour();

        $counts = DB::table('page_views')
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as count")
            ->where('created_at', '>=', $startHour)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour');

        $hours = [];
        $cursor = $startHour->copy();

        for ($i = 0; $i < 24; $i++) {
            $key = $cursor->format('Y-m-d H:00');
            $hours[] = [
                'hour' => $key,
                'count' => (int) ($counts[$key] ?? 0),
            ];
            $cursor->addHour();
        }

        return $hours;
    }

    /** @return list<array<string, mixed>> */
    private function topSites(AnalyticsDateRange $range): array
    {
        $since = $range->startUtc();
        $until = $range->endUtc();
        $totalEvents = (int) DB::table('page_views')
            ->where('created_at', '>=', $since)
            ->where('created_at', '<=', $until)
            ->count();

        $sites = DB::table('page_views')
            ->join('sites', 'page_views.site_id', '=', 'sites.id')
            ->join('users', 'sites.user_id', '=', 'users.id')
            ->select(
                'sites.id',
                'sites.name',
                'sites.domain',
                'sites.is_paused',
                'users.id as user_id',
                'users.email as owner_email',
                DB::raw('COUNT(*) as page_views'),
            )
            ->where('page_views.created_at', '>=', $since)
            ->where('page_views.created_at', '<=', $until)
            ->groupBy('sites.id', 'sites.name', 'sites.domain', 'sites.is_paused', 'users.id', 'users.email')
            ->orderByDesc('page_views')
            ->limit(10)
            ->get();

        return $sites->map(fn ($row) => [
            'id' => $row->id,
            'name' => $row->name,
            'domain' => $row->domain,
            'is_paused' => (bool) $row->is_paused,
            'user_id' => $row->user_id,
            'owner_email' => $row->owner_email,
            'page_views' => (int) $row->page_views,
            'share' => $totalEvents > 0
                ? round(($row->page_views / $totalEvents) * 100, 1)
                : 0.0,
        ])->all();
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function recentActivity(): array
    {
        $recentUsers = User::query()
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => DateFormatter::display($user->created_at),
            ])
            ->all();

        $recentSites = Site::query()
            ->with('user:id,email')
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'domain', 'user_id', 'created_at'])
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'owner_email' => $site->user?->email,
                'created_at' => DateFormatter::display($site->created_at),
            ])
            ->all();

        return [
            'users' => $recentUsers,
            'sites' => $recentSites,
        ];
    }

    private function pageViewsOnDate(Carbon $date): int
    {
        return (int) DB::table('page_views')
            ->whereDate('created_at', $date)
            ->count();
    }

    private function liveEvents(): int
    {
        return (int) DB::table('page_views')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->selectRaw('COUNT(DISTINCT '.AnalyticsSql::visitorFingerprintExpression().') as aggregate')
            ->value('aggregate');
    }

    /**
     * @param  Collection<string, mixed>|array<string, mixed>  $counts
     * @return list<array{date: string, count: int}>
     */
    private function fillDateRange(Carbon $startDate, int $days, Collection|array $counts): array
    {
        if ($counts instanceof Collection) {
            $counts = $counts->all();
        }

        $trend = [];
        $cursor = $startDate->copy();

        for ($i = 0; $i < $days; $i++) {
            $key = $cursor->toDateString();
            $trend[] = [
                'date' => $key,
                'count' => (int) ($counts[$key] ?? 0),
            ];
            $cursor->addDay();
        }

        return $trend;
    }
}
