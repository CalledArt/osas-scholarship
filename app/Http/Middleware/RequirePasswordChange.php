<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Routes a user with a pending forced password change may still reach.
     *
     * @var array<int, string>
     */
    private const EXEMPT_ROUTES = ['password.force-change', 'password.update', 'logout'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->must_change_password && ! $request->routeIs(...self::EXEMPT_ROUTES)) {
            return redirect()->route('password.force-change');
        }

        return $next($request);
    }
}
