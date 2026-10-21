<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Replaces TenancyServiceProvider in standalone mode.
 *
 * The original TenancyServiceProvider does three things:
 *   1. Registers Stancl tenancy lifecycle events (DB create/migrate/delete)
 *   2. Maps the four tenant route files with their prefixes
 *   3. Raises Stancl middleware priority
 *
 * None of that is needed when running as a single-tenant app.
 * This provider is a no-op placeholder so config/app.php stays valid.
 */
class StandaloneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
