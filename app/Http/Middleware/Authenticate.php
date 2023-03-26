<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Middleware\Authenticate as AuthenticateMiddleware;

class Authenticate extends AuthenticateMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     *
     * @throws AuthorizationException
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            if ($request->is('oauth/*')) {
                return route('login.cas', ['next' => url()->full()]);
            }

            throw new AuthorizationException('Unauthorized');
        }
    }
}
