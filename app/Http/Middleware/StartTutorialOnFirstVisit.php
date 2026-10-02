<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StartTutorialOnFirstVisit
{
    /**
     * Send a user to the practice tour the first time they reach their dashboard.
     *
     * Finishing or leaving the tour records it as seen, after which the
     * dashboard opens normally and the tour stays replayable from the rail.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->tutorial_completed_at === null) {
            return redirect()->route('tutorial');
        }

        return $next($request);
    }
}
