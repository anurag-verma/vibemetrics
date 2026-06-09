<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HealthController as AdminHealthController;
use App\Http\Controllers\Admin\LogController as AdminLogController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SiteController as AdminSiteController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\ErrorPreviewController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteSettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

$marketingProps = fn () => [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
];

Route::get('/', fn () => Inertia::render('Landing', $marketingProps()))->name('home');

Route::get('/features', fn () => Inertia::render('Marketing/Features', $marketingProps()))->name('features');

Route::get('/use-cases', fn () => Inertia::render('Marketing/UseCases/Index', $marketingProps()))->name('use-cases');

Route::get('/use-cases/{slug}', function (string $slug) use ($marketingProps) {
    if (! in_array($slug, ['saas', 'ecommerce', 'blogs', 'agencies'], true)) {
        abort(404);
    }

    return Inertia::render('Marketing/UseCases/Show', [
        ...$marketingProps(),
        'slug' => $slug,
    ]);
})->name('use-cases.show');

Route::get('/pricing', fn () => Inertia::render('Marketing/Pricing', $marketingProps()))->name('pricing');

Route::get('/docs', fn () => Inertia::render('Marketing/Docs', [
    ...$marketingProps(),
    'appUrl' => rtrim(config('app.url'), '/'),
]))->name('docs');

Route::get('/privacy', fn () => Inertia::render('Marketing/Privacy', $marketingProps()))->name('privacy');

Route::get('/terms', fn () => Inertia::render('Marketing/Terms', $marketingProps()))->name('terms');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/getting-started', OnboardingController::class)->name('getting-started');

    Route::get('/documentation', DocumentationController::class)->name('documentation');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::get('/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
    Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

    Route::get('/sites/{site}', [DashboardController::class, 'show'])->name('sites.show');
    Route::get('/sites/{site}/export', [ExportController::class, 'show'])
        ->middleware('throttle:10,1')
        ->name('sites.export');

    Route::get('/sites/{site}/edit', [SiteSettingsController::class, 'edit'])->name('sites.edit');
    Route::redirect('/sites/{site}/settings', '/sites/{site}/edit')->name('sites.settings');
    Route::patch('/sites/{site}', [SiteSettingsController::class, 'update'])->name('sites.update');
    Route::post('/sites/{site}/regenerate-tracking-id', [SiteSettingsController::class, 'regenerateTrackingId'])
        ->name('sites.regenerate-tracking-id');
    Route::post('/sites/{site}/reset', [SiteSettingsController::class, 'reset'])->name('sites.reset');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/health', [AdminHealthController::class, 'index'])->name('health.index');
        Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/sites', [AdminSiteController::class, 'index'])->name('sites.index');
        Route::patch('/sites/{site}', [AdminSiteController::class, 'update'])->name('sites.update');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/branding/{type}', [AdminSettingsController::class, 'uploadBranding'])->name('settings.branding.upload');
        Route::delete('/settings/branding/{type}', [AdminSettingsController::class, 'deleteBranding'])->name('settings.branding.delete');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

if (app()->environment(['local', 'testing'])) {
    Route::prefix('preview-errors')
        ->name('preview-errors.')
        ->group(function () {
            Route::get('/', [ErrorPreviewController::class, 'index'])->name('index');
            Route::get('/{status}', [ErrorPreviewController::class, 'show'])
                ->whereNumber('status')
                ->name('show');
        });
}

require __DIR__.'/auth.php';
