<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LogReaderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LogController extends Controller
{
    public function index(Request $request, LogReaderService $logs): Response
    {
        $validated = $request->validate([
            'file' => ['nullable', 'string', 'max:255', 'regex:/^laravel(-\d{4}-\d{2}-\d{2})?\.log$/'],
            'level' => ['nullable', 'string', 'max:20'],
            'search' => ['nullable', 'string', 'max:200'],
            'lines' => ['nullable', 'integer', 'min:100', 'max:2000'],
        ]);

        try {
            $snapshot = $logs->read(
                filename: $validated['file'] ?? null,
                level: $validated['level'] ?? null,
                search: $validated['search'] ?? null,
                lines: (int) ($validated['lines'] ?? 500),
            );
        } catch (FileException) {
            abort(404);
        }

        return Inertia::render('Admin/Logs/Index', [
            'files' => $logs->listFiles(),
            'levels' => $logs->levels(),
            'filters' => [
                'file' => $snapshot['file'] ?: ($validated['file'] ?? null),
                'level' => $validated['level'] ?? '',
                'search' => $validated['search'] ?? '',
                'lines' => (int) ($validated['lines'] ?? 500),
            ],
            'log' => $snapshot,
        ]);
    }
}
