<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'client.active' => \App\Http\Middleware\EnsureClientIsActive::class,
            'client.onboarded' => \App\Http\Middleware\EnsureClientOnboarded::class,
            'analytics.token' => \App\Http\Middleware\AnalyticsApiToken::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            return route('client.login');
        });

        // Where the "guest" middleware sends someone who is already signed in —
        // without this it falls back to the public landing page.
        $middleware->redirectUsersTo(function ($request) {
            if (auth('master_admin')->check()) {
                return route('admin.dashboard');
            }
            if (auth('client')->check()) {
                return route('client.dashboard');
            }
            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
