<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Subfission\Cas\CasServiceProvider::class,
        \RealRashid\SweetAlert\SweetAlertServiceProvider::class,
        \Spatie\Permission\PermissionServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up'
    )
    ->withMiddleware(static function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(static fn () => route('login.cas', ['next' => url()->full()]));

        $middleware->validateCsrfTokens(except: [
            'apiv3/*',
            'oauth/*',
        ]);

        $middleware->web([
            \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
            \App\Http\Middleware\Sentry::class,
            \Spatie\Csp\AddCspHeaders::class,
        ]);

        $middleware->throttleApi(
            limiter: '600|api_rate_limit,1',
            redis: env('CACHE_STORE') === 'redis'
        );
        $middleware->api(\App\Http\Middleware\Sentry::class);

        $middleware->alias([
            'auth.cas.check' => \App\Http\Middleware\CasCheck::class,
            'auth.cas.force' => \App\Http\Middleware\CasAuthenticate::class,
            'auth.sponsor' => \App\Http\Middleware\SponsorAuthenticate::class,
            'auth.user_or_client_token' => \App\Http\Middleware\AuthenticateWithUserOrClientToken::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'cache' => \Spatie\ResponseCache\Middlewares\CacheResponse::class,
        ]);

        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\CasAuthenticate::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\AuthenticateWithUserOrClientToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
        ]);
    })
    ->withExceptions(static function (Exceptions $exceptions): void {
        $exceptions->reportable(static function (Throwable $e): void {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    })->create();
