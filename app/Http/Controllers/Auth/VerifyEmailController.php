<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectAfterVerification($request);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectAfterVerification($request);
    }

    protected function redirectAfterVerification(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $message = 'Your email is verified. Welcome to VibeMetrics!';

        if ($user->isAdmin()) {
            return redirect()
                ->intended(route('admin.dashboard', absolute: false))
                ->with('success', $message);
        }

        if ($user->sites()->exists()) {
            $site = $user->sites()->latest()->first();

            return redirect()
                ->intended(route('sites.show', $site, absolute: false))
                ->with('success', $message);
        }

        return redirect()
            ->route('getting-started')
            ->with('success', $message);
    }
}
