<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthService;
use Inertia\Inertia;
use Inertia\Response;

class HealthController extends Controller
{
    public function index(SystemHealthService $health): Response
    {
        return Inertia::render('Admin/Health/Index', [
            'health' => $health->snapshot(),
        ]);
    }
}
