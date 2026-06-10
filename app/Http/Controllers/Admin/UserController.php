<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use App\Services\SiteLimitService;
use App\Services\TransactionalEmailService;
use App\Support\DateFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request, SiteLimitService $siteLimit): Response
    {
        $defaultSiteLimit = $siteLimit->platformDefault();

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
                'site_limit' => $user->site_limit,
                'effective_site_limit' => $siteLimit->isUnlimited($user) ? null : $siteLimit->maxFor($user),
                'is_unlimited_sites' => $siteLimit->isUnlimited($user),
                'created_at' => DateFormatter::display($user->created_at),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'defaultSiteLimit' => $defaultSiteLimit,
            'currentUserId' => $request->user()->id,
        ]);
    }

    public function update(
        UpdateAdminUserRequest $request,
        User $user,
        TransactionalEmailService $transactionalEmail,
    ): RedirectResponse {
        $wasActive = $user->is_active;

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

        if ($request->has('site_limit')) {
            $user->site_limit = $request->input('site_limit');
        }

        $user->save();

        if ($wasActive && ! $user->is_active) {
            $transactionalEmail->sendAccountDeactivated($user);
        }

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
