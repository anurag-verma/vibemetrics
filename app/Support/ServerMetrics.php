<?php

namespace App\Support;

class ServerMetrics
{
    /** @return array<string, string|null> */
    public function osInfo(): array
    {
        return [
            'family' => PHP_OS_FAMILY,
            'name' => php_uname('s'),
            'release' => php_uname('r'),
            'machine' => php_uname('m'),
            'hostname' => gethostname() ?: null,
            'label' => $this->osLabel(),
        ];
    }

    public function osLabel(): string
    {
        $family = PHP_OS_FAMILY;
        $name = php_uname('s');
        $release = php_uname('r');

        return trim("{$name} {$release}") !== ''
            ? trim("{$name} {$release}")
            : $family;
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $memory = $this->memory();
        $cpuPercent = $this->cpuUsagePercent();
        $load = $this->loadAverage();
        $uptimeSeconds = $this->uptimeSeconds();

        return [
            'cpu_percent' => $cpuPercent,
            'memory' => $memory,
            'load_average' => $load,
            'uptime_seconds' => $uptimeSeconds,
            'uptime_human' => $uptimeSeconds !== null ? $this->formatUptime($uptimeSeconds) : null,
        ];
    }

    public function cpuUsagePercent(): ?float
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => $this->windowsCpuPercent(),
            'Linux' => $this->linuxCpuPercent(),
            'Darwin' => $this->darwinCpuPercent(),
            default => null,
        };
    }

    /**
     * @return array{total_bytes: int, used_bytes: int, free_bytes: int, used_percent: float}|null
     */
    public function memory(): ?array
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => $this->windowsMemory(),
            'Linux' => $this->linuxMemory(),
            'Darwin' => $this->darwinMemory(),
            default => null,
        };
    }

    /**
     * @return array{1m: float, 5m: float, 15m: float}|null
     */
    public function loadAverage(): ?array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return null;
        }

        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();

            if (is_array($load) && count($load) >= 3) {
                return [
                    '1m' => round($load[0], 2),
                    '5m' => round($load[1], 2),
                    '15m' => round($load[2], 2),
                ];
            }
        }

        return null;
    }

    public function uptimeSeconds(): ?int
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => $this->windowsUptimeSeconds(),
            'Linux' => $this->linuxUptimeSeconds(),
            'Darwin' => $this->darwinUptimeSeconds(),
            default => null,
        };
    }

    public function formatUptime(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return "{$days}d {$hours}h";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    private function windowsCpuPercent(): ?float
    {
        $output = $this->runCommand('wmic cpu get loadpercentage /value 2>nul');

        if ($output === null) {
            return null;
        }

        if (preg_match('/LoadPercentage=(\d+)/i', $output, $matches)) {
            return round((float) $matches[1], 1);
        }

        return null;
    }

    private function linuxCpuPercent(): ?float
    {
        $sample = $this->readProcStatCpu();

        if ($sample === null) {
            return null;
        }

        usleep(150000);

        $next = $this->readProcStatCpu();

        if ($next === null) {
            return null;
        }

        $idleDelta = $next['idle'] - $sample['idle'];
        $totalDelta = $next['total'] - $sample['total'];

        if ($totalDelta <= 0) {
            return null;
        }

        return round((1 - ($idleDelta / $totalDelta)) * 100, 1);
    }

    private function darwinCpuPercent(): ?float
    {
        $output = $this->runCommand('ps -A -o %cpu 2>/dev/null | awk \'NR>1 {s+=$1} END {print s}\'');

        if ($output === null || ! is_numeric(trim($output))) {
            return null;
        }

        $cores = $this->cpuCoreCount() ?: 1;

        return round(min(100, (float) trim($output) / $cores), 1);
    }

    /**
     * @return array{idle: int, total: int}|null
     */
    private function readProcStatCpu(): ?array
    {
        if (! is_readable('/proc/stat')) {
            return null;
        }

        $line = strtok((string) file_get_contents('/proc/stat'), "\n");

        if (! is_string($line) || ! str_starts_with($line, 'cpu ')) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($line));

        if ($parts === false || count($parts) < 5) {
            return null;
        }

        $values = array_map('intval', array_slice($parts, 1));
        $idle = ($values[3] ?? 0) + ($values[4] ?? 0);
        $total = array_sum($values);

        return ['idle' => $idle, 'total' => $total];
    }

    /**
     * @return array{total_bytes: int, used_bytes: int, free_bytes: int, used_percent: float}|null
     */
    private function windowsMemory(): ?array
    {
        $output = $this->runCommand('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value 2>nul');

        if ($output === null) {
            return null;
        }

        $freeKb = null;
        $totalKb = null;

        if (preg_match('/FreePhysicalMemory=(\d+)/i', $output, $matches)) {
            $freeKb = (int) $matches[1];
        }

        if (preg_match('/TotalVisibleMemorySize=(\d+)/i', $output, $matches)) {
            $totalKb = (int) $matches[1];
        }

        if ($freeKb === null || $totalKb === null || $totalKb <= 0) {
            return null;
        }

        $totalBytes = $totalKb * 1024;
        $freeBytes = $freeKb * 1024;
        $usedBytes = max(0, $totalBytes - $freeBytes);

        return $this->memoryPayload($totalBytes, $usedBytes, $freeBytes);
    }

    /**
     * @return array{total_bytes: int, used_bytes: int, free_bytes: int, used_percent: float}|null
     */
    private function linuxMemory(): ?array
    {
        if (! is_readable('/proc/meminfo')) {
            return null;
        }

        $contents = file_get_contents('/proc/meminfo');

        if ($contents === false) {
            return null;
        }

        $values = [];

        foreach (explode("\n", $contents) as $line) {
            if (preg_match('/^([A-Za-z]+):\s+(\d+)/', $line, $matches)) {
                $values[$matches[1]] = (int) $matches[2];
            }
        }

        $totalKb = $values['MemTotal'] ?? null;

        if ($totalKb === null || $totalKb <= 0) {
            return null;
        }

        $availableKb = $values['MemAvailable'] ?? ($values['MemFree'] ?? null);

        if ($availableKb === null) {
            return null;
        }

        $totalBytes = $totalKb * 1024;
        $freeBytes = $availableKb * 1024;
        $usedBytes = max(0, $totalBytes - $freeBytes);

        return $this->memoryPayload($totalBytes, $usedBytes, $freeBytes);
    }

    /**
     * @return array{total_bytes: int, used_bytes: int, free_bytes: int, used_percent: float}|null
     */
    private function darwinMemory(): ?array
    {
        $totalOutput = $this->runCommand('sysctl -n hw.memsize 2>/dev/null');
        $pageSizeOutput = $this->runCommand('sysctl -n hw.pagesize 2>/dev/null');
        $freeOutput = $this->runCommand('vm_stat 2>/dev/null');

        if ($totalOutput === null || ! is_numeric(trim($totalOutput)) || $freeOutput === null) {
            return null;
        }

        $totalBytes = (int) trim($totalOutput);
        $pageSize = is_numeric(trim((string) $pageSizeOutput)) ? (int) trim((string) $pageSizeOutput) : 4096;
        $freePages = 0;

        if (preg_match('/Pages free:\s+(\d+)\./', $freeOutput, $matches)) {
            $freePages += (int) $matches[1];
        }

        if (preg_match('/Pages inactive:\s+(\d+)\./', $freeOutput, $matches)) {
            $freePages += (int) $matches[1];
        }

        $freeBytes = min($totalBytes, $freePages * $pageSize);
        $usedBytes = max(0, $totalBytes - $freeBytes);

        return $this->memoryPayload($totalBytes, $usedBytes, $freeBytes);
    }

    /**
     * @return array{total_bytes: int, used_bytes: int, free_bytes: int, used_percent: float}
     */
    private function memoryPayload(int $totalBytes, int $usedBytes, int $freeBytes): array
    {
        return [
            'total_bytes' => $totalBytes,
            'used_bytes' => $usedBytes,
            'free_bytes' => $freeBytes,
            'used_percent' => round(($usedBytes / max($totalBytes, 1)) * 100, 1),
        ];
    }

    private function windowsUptimeSeconds(): ?int
    {
        $output = $this->runCommand('wmic os get lastbootuptime /value 2>nul');

        if ($output === null || ! preg_match('/LastBootUpTime=(\d{14})/', $output, $matches)) {
            return null;
        }

        $boot = \DateTimeImmutable::createFromFormat('YmdHis', substr($matches[1], 0, 14));

        if ($boot === false) {
            return null;
        }

        return max(0, time() - $boot->getTimestamp());
    }

    private function linuxUptimeSeconds(): ?int
    {
        if (! is_readable('/proc/uptime')) {
            return null;
        }

        $contents = file_get_contents('/proc/uptime');

        if ($contents === false) {
            return null;
        }

        $seconds = (float) strtok($contents, ' ');

        return (int) round($seconds);
    }

    private function darwinUptimeSeconds(): ?int
    {
        $output = $this->runCommand('sysctl -n kern.boottime 2>/dev/null');

        if ($output === null || ! preg_match('/sec = (\d+)/', $output, $matches)) {
            return null;
        }

        return max(0, time() - (int) $matches[1]);
    }

    private function cpuCoreCount(): ?int
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $output = $this->runCommand('wmic cpu get NumberOfLogicalProcessors /value 2>nul');

            if ($output !== null && preg_match('/NumberOfLogicalProcessors=(\d+)/i', $output, $matches)) {
                return (int) $matches[1];
            }
        }

        if (is_readable('/proc/cpuinfo')) {
            preg_match_all('/^processor\s*:/m', (string) file_get_contents('/proc/cpuinfo'), $matches);

            return count($matches[0] ?? []) ?: null;
        }

        return null;
    }

    private function runCommand(string $command): ?string
    {
        if (! function_exists('shell_exec')) {
            return null;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        if (in_array('shell_exec', $disabled, true)) {
            return null;
        }

        try {
            $output = shell_exec($command);

            return is_string($output) && trim($output) !== '' ? trim($output) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
