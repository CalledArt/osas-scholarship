<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGoogleRegistration
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('google_registration.identity')
            || $request->session()->get('google_registration.expires_at', 0) <= now()->getTimestamp()) {
            $request->session()->forget('google_registration');

            return redirect()->route('login')->withErrors([
                'google' => __('Please continue with Google to verify your CvSU account before registering.'),
            ]);
        }

        return $next($request);
    }
}
