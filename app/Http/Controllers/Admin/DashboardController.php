<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, PlatformAnalyticsService $analytics): Response
    {
        $range = (int) $request->query('range', 30);

        return Inertia::render('Admin/Dashboard', $analytics->overview($range));
    }
}
