<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RequirePasswordChange;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Vercel terminates TLS and forwards plain HTTP into the container.
        // Without trusting its forwarded headers Laravel believes the request is
        // http://, then generates http asset URLs the browser blocks as mixed
        // content and rejects the session cookie. Nothing reaches the container
        // except through Vercel, so the forwarded headers can be taken at face
        // value.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->appendToGroup('web', [
            RequirePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
