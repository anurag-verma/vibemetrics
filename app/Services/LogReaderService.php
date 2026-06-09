<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LogReaderService
{
    private const FILENAME_PATTERN = '/^laravel(-\d{4}-\d{2}-\d{2})?\.log$/';

    private const LEVELS = [
        'EMERGENCY',
        'ALERT',
        'CRITICAL',
        'ERROR',
        'WARNING',
        'NOTICE',
        'INFO',
        'DEBUG',
    ];

    private const MAX_TAIL_BYTES = 524288;

    private const DEFAULT_LINES = 500;

    private const MAX_LINES = 2000;

    /** @return list<array{name: string, path: string, size_bytes: int, modified_at: string|null}> */
    public function listFiles(): array
    {
        $directory = storage_path('logs');

        if (! is_dir($directory)) {
            return [];
        }

        $files = glob($directory.DIRECTORY_SEPARATOR.'laravel*.log') ?: [];
        $entries = [];

        foreach ($files as $path) {
            $name = basename($path);

            if (! $this->isAllowedFilename($name) || ! is_readable($path)) {
                continue;
            }

            $modified = @filemtime($path);

            $entries[] = [
                'name' => $name,
                'path' => $path,
                'size_bytes' => (int) (@filesize($path) ?: 0),
                'modified_at' => $modified ? Carbon::createFromTimestamp($modified)->toIso8601String() : null,
            ];
        }

        usort($entries, fn (array $a, array $b) => strcmp($b['name'], $a['name']));

        return $entries;
    }

    /**
     * @return array{
     *     file: string,
     *     size_bytes: int,
     *     modified_at: string|null,
     *     truncated: bool,
     *     entries: list<array{timestamp: string|null, level: string|null, environment: string|null, message: string, lines: list<string>}>,
     *     content: string
     * }
     */
    public function read(
        ?string $filename = null,
        ?string $level = null,
        ?string $search = null,
        int $lines = self::DEFAULT_LINES,
    ): array {
        $files = $this->listFiles();

        if ($files === []) {
            return [
                'file' => '',
                'size_bytes' => 0,
                'modified_at' => null,
                'truncated' => false,
                'entries' => [],
                'content' => '',
            ];
        }

        $selected = $this->resolveFile($files, $filename);
        $path = $selected['path'];
        $tail = $this->tail($path);
        $entries = $this->parseEntries($tail['content']);
        $entries = $this->filterEntries($entries, $level, $search);
        $entries = array_slice($entries, -$this->normalizeLines($lines));
        $outputLines = [];

        foreach ($entries as $entry) {
            foreach ($entry['lines'] as $line) {
                $outputLines[] = $line;
            }
        }

        $content = implode("\n", $outputLines);

        return [
            'file' => $selected['name'],
            'size_bytes' => $selected['size_bytes'],
            'modified_at' => $selected['modified_at'],
            'truncated' => $tail['truncated'],
            'entries' => array_values($entries),
            'content' => $content,
        ];
    }

    /** @return list<string> */
    public function levels(): array
    {
        return self::LEVELS;
    }

    public function isAllowedFilename(string $filename): bool
    {
        return (bool) preg_match(self::FILENAME_PATTERN, $filename);
    }

    /**
     * @param  list<array{name: string, path: string, size_bytes: int, modified_at: string|null}>  $files
     * @return array{name: string, path: string, size_bytes: int, modified_at: string|null}
     */
    private function resolveFile(array $files, ?string $filename): array
    {
        if ($filename !== null) {
            if (! $this->isAllowedFilename($filename)) {
                throw new FileException('Invalid log file name.');
            }

            foreach ($files as $file) {
                if ($file['name'] === $filename) {
                    return $file;
                }
            }

            throw new FileException('Log file not found.');
        }

        return $files[0];
    }

    /**
     * @return array{content: string, truncated: bool}
     */
    private function tail(string $path): array
    {
        $size = (int) (@filesize($path) ?: 0);

        if ($size === 0) {
            return ['content' => '', 'truncated' => false];
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new FileException('Unable to read log file.');
        }

        $readBytes = min($size, self::MAX_TAIL_BYTES);
        fseek($handle, -$readBytes, SEEK_END);
        $content = (string) fread($handle, $readBytes);
        fclose($handle);

        $truncated = $readBytes < $size;

        if ($truncated) {
            $content = preg_replace('/^[^\n]*\n?/', '', $content, 1) ?? $content;
        }

        return [
            'content' => rtrim($content, "\r\n"),
            'truncated' => $truncated,
        ];
    }

    /**
     * @return list<array{timestamp: string|null, level: string|null, environment: string|null, message: string, lines: list<string>}>
     */
    private function parseEntries(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $entries = [];
        $current = null;

        foreach (preg_split('/\r\n|\n|\r/', $content) as $line) {
            if (preg_match('/^\[([^\]]+)\]\s+(\S+)\.(\w+):\s*(.*)$/', $line, $matches)) {
                if ($current !== null) {
                    $entries[] = $current;
                }

                $current = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => strtoupper($matches[3]),
                    'message' => $matches[4],
                    'lines' => [$line],
                ];

                continue;
            }

            if ($current !== null) {
                $current['lines'][] = $line;
                $current['message'] .= $line === '' ? "\n" : "\n".$line;
            }
        }

        if ($current !== null) {
            $entries[] = $current;
        }

        return $entries;
    }

    /**
     * @param  list<array{timestamp: string|null, level: string|null, environment: string|null, message: string, lines: list<string>}>  $entries
     * @return list<array{timestamp: string|null, level: string|null, environment: string|null, message: string, lines: list<string>}>
     */
    private function filterEntries(array $entries, ?string $level, ?string $search): array
    {
        $level = $level !== null && $level !== '' ? strtoupper($level) : null;
        $search = $search !== null ? trim($search) : '';

        return array_values(array_filter($entries, function (array $entry) use ($level, $search) {
            if ($level !== null && ($entry['level'] ?? null) !== $level) {
                return false;
            }

            if ($search !== '' && stripos($entry['message'], $search) === false) {
                return false;
            }

            return true;
        }));
    }

    private function normalizeLines(int $lines): int
    {
        return max(100, min(self::MAX_LINES, $lines));
    }
}
