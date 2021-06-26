<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Middleware\Authenticate as AuthenticateMiddleware;

class Authenticate extends AuthenticateMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     * @throws AuthorizationException
     */
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('oauth/*')) {
                return route('login.cas', ['next' => url()->full()]);
            } else {
                throw new AuthorizationException('Unauthorized');
            }
        }
        // @phpstan-ignore-line
    }
}
