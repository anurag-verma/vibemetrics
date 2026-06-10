<?php

namespace App\Providers;

use App\Listeners\SendPasswordChangedEmail;
use App\Listeners\SendWelcomeEmail;
use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('collect', function (Request $request) {
            $perMinute = app(PlatformSettingsService::class)->getInt('collect_rate_limit', 120);
            $perMinute = max(10, min(1000, $perMinute));
            $trackingId = (string) $request->input('tracking_id', 'unknown');
            $sitePerMinute = max($perMinute, min(5000, $perMinute * 5));

            return [
                Limit::perMinute($perMinute)->by($request->ip()),
                Limit::perMinute($sitePerMinute)->by('collect:site:'.$trackingId),
            ];
        });

        Vite::prefetch(concurrency: 3);

        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();

            return $this->app->isProduction()
                ? $rule->uncompromised()
                : $rule;
        });

        Event::listen(Registered::class, SendWelcomeEmail::class);
        Event::listen(PasswordReset::class, SendPasswordChangedEmail::class);

        $branding = fn (): array => app(BrandingService::class)->toArray();

        View::composer([
            'app',
            'errors.*',
            'mail::*',
            'vendor.mail.*',
            'vendor.notifications.*',
        ], function ($view) use ($branding): void {
            $view->with('branding', $branding());
        });
    }
}
