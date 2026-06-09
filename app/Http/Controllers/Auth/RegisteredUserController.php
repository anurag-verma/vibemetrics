<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Models\User;
use App\Services\PlatformSettingsService;
use App\Support\TimezoneList;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RegisteredUserController extends Controller
{
    public function create(PlatformSettingsService $settings): Response|RedirectResponse
    {
        if (! $settings->getBool('registration_enabled', true)) {
            return redirect()->route('login')->with('error', 'Registration is currently disabled.');
        }

        return Inertia::render('Auth/Register');
    }

    public function store(RegisterUserRequest $request, PlatformSettingsService $settings): RedirectResponse
    {
        if (! $settings->getBool('registration_enabled', true)) {
            abort(HttpResponse::HTTP_FORBIDDEN, 'Registration is currently disabled.');
        }

        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'timezone' => TimezoneList::resolve($validated['timezone'] ?? null),
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('verification.notice'));
    }
}
