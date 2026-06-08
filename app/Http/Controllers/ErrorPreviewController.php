<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ErrorPreviewController extends Controller
{
    /** @var list<int> */
    private const INERTIA_STATUSES = [403, 404, 500, 503];

    public function index(): InertiaResponse
    {
        return Inertia::render('Dev/ErrorPreviewIndex', [
            'statuses' => [
                ['code' => 403, 'label' => 'Access denied'],
                ['code' => 404, 'label' => 'Page not found'],
                ['code' => 419, 'label' => 'Session expired (Blade)'],
                ['code' => 500, 'label' => 'Server error'],
                ['code' => 503, 'label' => 'Unavailable'],
            ],
        ]);
    }

    public function show(int $status): SymfonyResponse|Response
    {
        if ($status === 419) {
            return response()->view('errors.419', [], 419);
        }

        if (! in_array($status, self::INERTIA_STATUSES, true)) {
            abort(404);
        }

        return Inertia::render('Error', [
            'status' => $status,
        ])
            ->toResponse(request())
            ->setStatusCode($status);
    }
}
