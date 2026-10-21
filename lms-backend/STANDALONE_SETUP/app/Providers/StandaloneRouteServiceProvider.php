<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Replaces RouteServiceProvider in standalone mode.
 *
 * Differences from the original:
 *  - No central-domain filtering (there is only one domain now)
 *  - Loads the standalone_* route files instead of the tenancy-wrapped originals
 *  - Prefixes match what TenancyServiceProvider previously handled:
 *      v1/admin      → routes/standalone_admin.php
 *      v1/instructor → routes/standalone_instructor.php
 *      v1/user       → routes/standalone_user.php
 *      v1            → routes/standalone_public.php
 */
class StandaloneRouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Central API (CSP report only)
            Route::prefix('v1')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // Admin panel routes
            Route::prefix('v1/admin')
                ->middleware('api')
                ->group(base_path('routes/standalone_admin.php'));

            // Instructor routes
            Route::prefix('v1/instructor')
                ->middleware('api')
                ->group(base_path('routes/standalone_instructor.php'));

            // Student (user) routes
            Route::prefix('v1/user')
                ->middleware('api')
                ->group(base_path('routes/standalone_user.php'));

            // Public tenant routes (auth, course catalog, etc.)
            Route::prefix('v1')
                ->middleware('api')
                ->group(base_path('routes/standalone_public.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
