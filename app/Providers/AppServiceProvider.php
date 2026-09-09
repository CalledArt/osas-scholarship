<?php

namespace App\Providers;

use App\Enums\UserRole;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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
