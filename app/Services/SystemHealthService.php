<?php

namespace App\Services;

use App\Support\ServerMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SystemHealthService
{
    private const CACHE_KEY = 'admin_system_health';

    private const CACHE_TTL = 30;

    public function __construct(
        private PlatformSettingsService $settings,
        private ServerMetrics $serverMetrics,
    ) {}

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $checks = [
                'server' => $this->serverCheck(),
                'database' => $this->databaseCheck(),
                'cache' => $this->cacheCheck(),
                'queue' => $this->queueCheck(),
                'storage' => $this->storageCheck(),
                'scheduler' => $this->schedulerCheck(),
                'opcache' => $this->opcacheCheck(),
                'mail' => $this->mailCheck(),
                'ingest' => $this->ingestCheck(),
            ];

            return [
                'status' => $this->overallStatus($checks),
                'checked_at' => now()->toIso8601String(),
                'app' => $this->appInfo(),
                'os' => $this->serverMetrics->osInfo(),
                'checks' => $checks,
                'tables' => $this->tableStats(),
            ];
        });
    }

    /** @param  array<string, array<string, mixed>>  $checks */
    private function overallStatus(array $checks): string
    {
        $statuses = array_column($checks, 'status');

        if (in_array('unhealthy', $statuses, true)) {
            return 'unhealthy';
        }

        if (in_array('degraded', $statuses, true)) {
            return 'degraded';
        }

        return 'healthy';
    }

    /** @return array<string, mixed> */
    private function appInfo(): array
    {
        return [
            'name' => config('app.name'),
            'version' => config('app.version'),
            'env' => config('app.env'),
            'debug' => (bool) config('app.debug'),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 1),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
            'memory_limit' => ini_get('memory_limit') ?: null,
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'https' => str_starts_with((string) config('app.url'), 'https://'),
        ];
    }

    /** @return array<string, mixed> */
    private function serverCheck(): array
    {
        $metrics = $this->serverMetrics->snapshot();
        $cpu = $metrics['cpu_percent'];
        $memory = $metrics['memory'];
        $status = 'healthy';
        $messages = [];

        if ($cpu !== null) {
            if ($cpu >= 95) {
                $status = 'unhealthy';
                $messages[] = "CPU {$cpu}%";
            } elseif ($cpu >= 85) {
                $status = 'degraded';
                $messages[] = "CPU {$cpu}%";
            }
        }

        if ($memory !== null) {
            $memPercent = $memory['used_percent'];

            if ($memPercent >= 95) {
                $status = 'unhealthy';
                $messages[] = "RAM {$memPercent}%";
            } elseif ($memPercent >= 85 && $status !== 'unhealthy') {
                $status = 'degraded';
                $messages[] = "RAM {$memPercent}%";
            }
        }

        if ($cpu === null && $memory === null) {
            $messages[] = 'Host metrics unavailable';
        }

        return [
            'status' => $status,
            'cpu_percent' => $cpu,
            'memory' => $memory,
            'load_average' => $metrics['load_average'],
            'uptime_seconds' => $metrics['uptime_seconds'],
            'uptime_human' => $metrics['uptime_human'],
            'message' => $messages !== []
                ? implode(' · ', $messages)
                : ($metrics['uptime_human'] ? "Up {$metrics['uptime_human']}" : 'Host metrics OK'),
        ];
    }

    /** @return array<string, mixed> */
    private function databaseCheck(): array
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}");

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latencyMs = round((microtime(true) - $start) * 1000, 1);

            return [
                'status' => $latencyMs > 500 ? 'degraded' : 'healthy',
                'driver' => $connection['driver'] ?? $driver,
                'database' => $connection['database'] ?? null,
                'latency_ms' => $latencyMs,
                'size_bytes' => $this->databaseSizeBytes($driver, $connection),
                'message' => 'Connected',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unhealthy',
                'driver' => $connection['driver'] ?? $driver,
                'database' => $connection['database'] ?? null,
                'latency_ms' => null,
                'size_bytes' => null,
                'message' => Str::limit($e->getMessage(), 120),
            ];
        }
    }

    /** @param  array<string, mixed>|null  $connection */
    private function databaseSizeBytes(string $driver, ?array $connection): ?int
    {
        if ($connection === null) {
            return null;
        }

        if (($connection['driver'] ?? null) === 'sqlite') {
            $path = $connection['database'] ?? null;

            if ($path && $path !== ':memory:' && is_file($path)) {
                return filesize($path) ?: null;
            }

            return null;
        }

        try {
            $database = $connection['database'] ?? null;

            if (! $database) {
                return null;
            }

            $result = DB::selectOne(
                'SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = ?',
                [$database]
            );

            return isset($result->size) ? (int) $result->size : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    private function cacheCheck(): array
    {
        $store = config('cache.default');
        $probeKey = 'health_probe_'.Str::random(8);
        $probeValue = 'ok';

        try {
            $start = microtime(true);
            Cache::put($probeKey, $probeValue, 10);
            $read = Cache::get($probeKey);
            Cache::forget($probeKey);
            $latencyMs = round((microtime(true) - $start) * 1000, 1);

            if ($read !== $probeValue) {
                return [
                    'status' => 'unhealthy',
                    'store' => $store,
                    'latency_ms' => $latencyMs,
                    'message' => 'Read/write mismatch',
                ];
            }

            return [
                'status' => $latencyMs > 250 ? 'degraded' : 'healthy',
                'store' => $store,
                'latency_ms' => $latencyMs,
                'message' => 'Read/write OK',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'unhealthy',
                'store' => $store,
                'latency_ms' => null,
                'message' => Str::limit($e->getMessage(), 120),
            ];
        }
    }

    /** @return array<string, mixed> */
    private function queueCheck(): array
    {
        $connection = config('queue.default');

        try {
            $pending = Schema::hasTable('jobs')
                ? (int) DB::table('jobs')->count()
                : 0;

            $failed = Schema::hasTable('failed_jobs')
                ? (int) DB::table('failed_jobs')->count()
                : 0;

            $status = 'healthy';
            $messages = [];

            if ($failed > 0) {
                $status = 'degraded';
                $messages[] = "{$failed} failed job(s)";
            }

            if ($pending > 1000) {
                $status = 'degraded';
                $messages[] = "{$pending} pending job(s)";
            }

            if ($connection === 'sync' && app()->environment('production')) {
                $status = 'degraded';
                $messages[] = 'Sync driver in production';
            } elseif ($connection === 'sync') {
                $messages[] = 'Inline sync driver (local)';
            }

            return [
                'status' => $status,
                'connection' => $connection,
                'pending_jobs' => $pending,
                'failed_jobs' => $failed,
                'worker_required' => $connection !== 'sync',
                'message' => $messages !== []
                    ? implode(' · ', $messages)
                    : 'No backlog',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'degraded',
                'connection' => $connection,
                'pending_jobs' => null,
                'failed_jobs' => null,
                'message' => Str::limit($e->getMessage(), 120),
            ];
        }
    }

    /** @return array<string, mixed> */
    private function storageCheck(): array
    {
        $path = storage_path();
        $freeBytes = @disk_free_space($path);
        $totalBytes = @disk_total_space($path);

        if ($freeBytes === false || $totalBytes === false || $totalBytes <= 0) {
            return [
                'status' => 'degraded',
                'free_bytes' => null,
                'total_bytes' => null,
                'used_percent' => null,
                'log_size_bytes' => $this->logSizeBytes(),
                'message' => 'Disk metrics unavailable',
            ];
        }

        $usedPercent = round((1 - ($freeBytes / $totalBytes)) * 100, 1);
        $status = match (true) {
            $usedPercent >= 95 => 'unhealthy',
            $usedPercent >= 85 => 'degraded',
            default => 'healthy',
        };

        return [
            'status' => $status,
            'free_bytes' => (int) $freeBytes,
            'total_bytes' => (int) $totalBytes,
            'used_percent' => $usedPercent,
            'log_size_bytes' => $this->logSizeBytes(),
            'message' => "{$usedPercent}% used",
        ];
    }

    private function logSizeBytes(): ?int
    {
        $logPath = storage_path('logs/laravel.log');

        if (! is_file($logPath)) {
            return 0;
        }

        return filesize($logPath) ?: 0;
    }

    /** @return array<string, mixed> */
    private function schedulerCheck(): array
    {
        $rollupEnabled = $this->settings->getBool('rollup_enabled', true);
        $rollupAt = $this->settings->get('last_rollup_at');
        $purgeAt = $this->settings->get('last_purge_at');

        $rollupStale = $rollupEnabled && $this->isStale($rollupAt, hours: 26);
        $purgeStale = $this->isStale($purgeAt, hours: 24 * 8);

        $status = 'healthy';
        $messages = [];

        if ($rollupStale) {
            $status = 'degraded';
            $messages[] = $rollupAt ? 'Rollup overdue' : 'Rollup never recorded';
        } elseif (! $rollupEnabled) {
            $messages[] = 'Rollup disabled';
        }

        if ($purgeStale) {
            $status = 'degraded';
            $messages[] = $purgeAt ? 'Purge overdue' : 'Purge never recorded';
        }

        return [
            'status' => $status,
            'rollup_enabled' => $rollupEnabled,
            'last_rollup_at' => $rollupAt,
            'last_purge_at' => $purgeAt,
            'rollup_schedule' => 'Daily at 01:00',
            'purge_schedule' => 'Weekly',
            'message' => $messages !== [] ? implode(' · ', $messages) : 'Tasks on schedule',
        ];
    }

    private function isStale(mixed $timestamp, int $hours): bool
    {
        if (! $timestamp) {
            return true;
        }

        try {
            return now()->diffInHours(Carbon::parse($timestamp)) >= $hours;
        } catch (\Throwable) {
            return true;
        }
    }

    /** @return array<string, mixed> */
    private function opcacheCheck(): array
    {
        if (! function_exists('opcache_get_status')) {
            return [
                'status' => 'degraded',
                'enabled' => false,
                'message' => 'OPcache extension unavailable',
            ];
        }

        $status = @opcache_get_status(false);

        if (! is_array($status) || empty($status['opcache_enabled'])) {
            return [
                'status' => 'degraded',
                'enabled' => false,
                'message' => 'OPcache disabled',
            ];
        }

        $memory = $status['memory_usage'] ?? [];
        $stats = $status['opcache_statistics'] ?? [];
        $usedPercent = isset($memory['used_memory'], $memory['free_memory'])
            ? round(($memory['used_memory'] / max($memory['used_memory'] + $memory['free_memory'], 1)) * 100, 1)
            : null;
        $hitRate = isset($stats['opcache_hit_rate'])
            ? round((float) $stats['opcache_hit_rate'], 1)
            : null;

        return [
            'status' => 'healthy',
            'enabled' => true,
            'used_percent' => $usedPercent,
            'hit_rate' => $hitRate,
            'cached_scripts' => $stats['num_cached_scripts'] ?? null,
            'message' => $hitRate !== null ? "Hit rate {$hitRate}%" : 'Enabled',
        ];
    }

    /** @return array<string, mixed> */
    private function mailCheck(): array
    {
        $driver = config('mail.default');
        $from = config('mail.from.address');
        $issues = [];

        if (! is_string($from) || $from === '') {
            $issues[] = 'From address missing';
        }

        if ($driver === 'log' && app()->environment('production')) {
            $issues[] = 'Log driver in production';
        }

        if ($driver === 'array') {
            $issues[] = 'Array driver discards mail';
        }

        return [
            'status' => $issues !== [] ? 'degraded' : 'healthy',
            'driver' => $driver,
            'from' => $from,
            'message' => $issues !== [] ? implode(' · ', $issues) : 'Configured',
        ];
    }

    /** @return array<string, mixed> */
    private function ingestCheck(): array
    {
        $maintenance = $this->settings->getBool('maintenance_mode');
        $rateLimit = $this->settings->getInt('collect_rate_limit', 120);
        $lastEventAt = Schema::hasTable('page_views')
            ? DB::table('page_views')->max('created_at')
            : null;

        $eventsLastDay = Schema::hasTable('page_views')
            ? (int) DB::table('page_views')->where('created_at', '>=', now()->subDay())->count()
            : 0;

        $status = $maintenance ? 'degraded' : 'healthy';

        return [
            'status' => $status,
            'maintenance_mode' => $maintenance,
            'collect_rate_limit' => $rateLimit,
            'events_last_24h' => $eventsLastDay,
            'last_event_at' => $lastEventAt,
            'message' => $maintenance
                ? 'Collect API in maintenance mode'
                : ($eventsLastDay > 0 ? "{$eventsLastDay} events in 24h" : 'No events in 24h'),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function tableStats(): array
    {
        $tables = [
            'page_views' => 'Page views',
            'daily_stats' => 'Daily stats',
            'sites' => 'Sites',
            'users' => 'Users',
            'jobs' => 'Queue jobs',
            'failed_jobs' => 'Failed jobs',
        ];

        $stats = [];

        foreach ($tables as $table => $label) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                $stats[] = [
                    'table' => $table,
                    'label' => $label,
                    'rows' => (int) DB::table($table)->count(),
                ];
            } catch (\Throwable) {
                $stats[] = [
                    'table' => $table,
                    'label' => $label,
                    'rows' => null,
                ];
            }
        }

        return $stats;
    }
}
