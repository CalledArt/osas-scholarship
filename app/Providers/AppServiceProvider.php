<?php

namespace App\Providers;

use App\Enums\UserRole;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

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
        // Every round trip to Supabase costs ~240ms (see .ai/rules/controllers.md),
        // so a relationship a controller forgot to eager load is not a slightly
        // slower page, it is a visibly broken one. These two settings are split by
        // environment on purpose, and are mutually exclusive: the autoloader runs
        // before the lazy-loading guard is ever consulted, so enabling both would
        // leave the guard permanently unreachable.
        //
        // Production absorbs the miss — one batched query for the whole collection
        // instead of one per row. Everywhere else the same miss throws, so it gets
        // fixed at its source instead of silently costing a round trip forever.
        Model::automaticallyEagerLoadRelationships($this->app->isProduction());
        Model::preventLazyLoading(! $this->app->isProduction());

        // The framework's default guest-middleware redirect always targets
        // the route literally named "dashboard" (student-only), so an
        // already-authenticated admin visiting a guest route (e.g. /login)
        // would be sent there and hit a 403 instead of their own dashboard.
        RedirectIfAuthenticated::redirectUsing(
            fn (Request $request) => $request->user()?->role === UserRole::Admin
                ? route('admin.dashboard')
                : route('dashboard')
        );
    }
}
