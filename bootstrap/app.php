<?php

use App\Http\Middleware\EnsureStoreAccess;
use App\Http\Middleware\EnsureUserIsOwner;
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
    ->withMiddleware(function (Middleware $middleware) {
        // Trust the platform's reverse proxy (Railway, etc.) so Laravel knows the
        // original request was HTTPS - otherwise generated asset/route URLs come
        // back as http:// and get blocked as mixed content by the browser.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'owner' => EnsureUserIsOwner::class,
            'store.access' => EnsureStoreAccess::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->wantsJson(),
        );
    })->create();
