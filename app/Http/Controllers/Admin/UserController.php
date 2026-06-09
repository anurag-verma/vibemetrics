<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Services\SiteLimitService;
use App\Support\DateFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, SiteLimitService $siteLimit): Response
    {
        $users = User::query()
            ->withCount('sites')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'email_verified' => $user->hasVerifiedEmail(),
                'is_active' => $user->is_active,
                'sites_count' => $user->sites_count,
                'created_at' => DateFormatter::display($user->created_at),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'siteLimit' => $siteLimit->maxFor($request->user()),
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            if ($request->has('is_admin') && ! $request->boolean('is_admin')) {
                return back()->with('error', 'You cannot remove your own admin access.');
            }

            if ($request->has('is_active') && ! $request->boolean('is_active')) {
                return back()->with('error', 'You cannot disable your own account.');
            }
        }

        if ($request->has('is_admin')) {
            $user->is_admin = $request->boolean('is_admin');
        }

        if ($request->has('email_verified')) {
            if ($request->boolean('email_verified')) {
                $user->markEmailAsVerified();
            } else {
                $user->markEmailAsUnverified();
            }
        }

        if ($request->has('is_active')) {
            $user->is_active = $request->boolean('is_active');
        }

        $user->save();

        return back()->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
            return back()->with('error', 'You cannot delete the last admin account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
