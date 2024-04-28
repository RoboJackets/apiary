<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login.cas',['next'=>url()->full()]));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->validateCsrfTokens(except: [
            'apiv3/*',
        ]);

        $middleware->web([
            \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
            \App\Http\Middleware\Sentry::class,
            \Spatie\Csp\AddCspHeaders::class,
            \HTMLMin\HTMLMin\Http\Middleware\MinifyMiddleware::class,
        ]);

        $middleware->throttleApi('180,1');
        $middleware->api(\App\Http\Middleware\Sentry::class);

        $middleware->alias([
            'auth.cas.check' => \App\Http\Middleware\CasCheck::class,
            'auth.cas.force' => \App\Http\Middleware\CasAuthenticate::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);

        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\Authenticate::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
