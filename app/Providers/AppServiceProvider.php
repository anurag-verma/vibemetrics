<?php

namespace App\Providers;

use App\Services\BrandingService;
use App\Services\PlatformSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
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

            return Limit::perMinute($perMinute)->by($request->ip());
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

        View::composer([
            'app',
            'errors.*',
            'vendor.mail.html.message',
            'vendor.mail.html.layout',
            'vendor.mail.text.message',
        ], function ($view) {
            $view->with('branding', app(BrandingService::class)->toArray());
        });
    }
}
