<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\DateFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    public function index(Request $request): Response
    {
        $sites = Site::query()
            ->with('user:id,name,email')
            ->withCount([
                'pageViews as events_7d' => fn ($query) => $query->where('created_at', '>=', now()->subDays(6)->startOfDay()),
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'is_paused' => $site->is_paused,
                'owner_name' => $site->user?->name,
                'owner_email' => $site->user?->email,
                'events_7d' => (int) $site->events_7d,
                'created_at' => DateFormatter::display($site->created_at),
            ]);

        return Inertia::render('Admin/Sites/Index', [
            'sites' => $sites,
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $request->validate([
            'is_paused' => ['required', 'boolean'],
        ]);

        $site->update(['is_paused' => $request->boolean('is_paused')]);

        Cache::forget("user_sites:{$site->user_id}");

        return back()->with('success', $site->is_paused ? 'Site tracking paused.' : 'Site tracking resumed.');
    }
}
