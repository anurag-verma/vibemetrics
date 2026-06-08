<?php

namespace App\Services;

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
    ) {}

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $checks = [
                'database' => $this->databaseCheck(),
                'cache' => $this->cacheCheck(),
                'queue' => $this->queueCheck(),
                'storage' => $this->storageCheck(),
                'scheduler' => $this->schedulerCheck(),
            ];

            return [
                'status' => $this->overallStatus($checks),
                'checked_at' => now()->toIso8601String(),
                'app' => $this->appInfo(),
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
            'env' => config('app.env'),
            'debug' => (bool) config('app.debug'),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 1),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
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

            if ($failed > 0) {
                $status = 'degraded';
            }

            if ($pending > 1000) {
                $status = 'degraded';
            }

            return [
                'status' => $status,
                'connection' => $connection,
                'pending_jobs' => $pending,
                'failed_jobs' => $failed,
                'message' => $failed > 0
                    ? "{$failed} failed job(s)"
                    : ($pending > 0 ? "{$pending} pending job(s)" : 'No backlog'),
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
            return now()->diffInHours(\Carbon\Carbon::parse($timestamp)) >= $hours;
        } catch (\Throwable) {
            return true;
        }
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
