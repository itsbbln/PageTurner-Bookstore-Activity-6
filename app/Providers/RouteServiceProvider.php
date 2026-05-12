<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // Backwards-compatible default limiter (standard)
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-tier', function (Request $request) {
            $user = $request->user();
            $key = $user?->id ? 'user:' . $user->id : 'ip:' . $request->ip();

            $tier = 'public';
            if ($user) {
                $tier = match ($user->role) {
                    'admin' => 'admin',
                    'premium' => 'premium',
                    default => 'standard',
                };
            }

            $perMinute = match ($tier) {
                'admin' => 1000,
                'premium' => 300,
                'standard' => 60,
                default => 30,
            };

            // Laravel 10's Limit does not support per-second limits directly.
            // Per-second burst protection is handled by a dedicated middleware.
            return Limit::perMinute($perMinute)->by($key);
        });

        RateLimiter::for('auth', function (Request $request) {
            $key = 'auth:' . ($request->ip());

            // Very strict: 10/min. Burst protection handled by middleware.
            return Limit::perMinute(10)->by($key);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
