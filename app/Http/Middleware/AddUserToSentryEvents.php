<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

namespace App\Http\Middleware;

use Closure;
use Sentry\State\Scope;

class AddUserToSentryEvents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check() && app()->bound('sentry')) {
            \Sentry\configureScope(static function (Scope $scope): void {
                $scope->setUser([
                    'id' => auth()->user()->id,
                    'username' => auth()->user()->uid,
                ]);
            });
        }

        return $next($request);
    }
}
