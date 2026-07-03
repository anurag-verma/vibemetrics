<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'match_type'  => ['required', 'in:exact,contains'],
            'url_pattern' => ['required', 'string', 'max:2048'],
        ]);

        $site->goals()->create($data);

        return back()->with('success', 'Goal created.');
    }

    public function update(Request $request, Site $site, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $site);
        abort_if($goal->site_id !== $site->id, 404);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'match_type'  => ['required', 'in:exact,contains'],
            'url_pattern' => ['required', 'string', 'max:2048'],
        ]);

        $goal->update($data);

        return back()->with('success', 'Goal updated.');
    }

    public function destroy(Site $site, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $site);
        abort_if($goal->site_id !== $site->id, 404);

        $goal->delete();

        return back()->with('success', 'Goal deleted.');
    }
}
