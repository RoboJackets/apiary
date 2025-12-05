<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Symfony\Component\HttpFoundation\Response;

/**
 * Try to authenticate an incoming request with either a client-specific (client_credentials grant) or user-specific
 * (authorization_code or personal_access grant) token.
 *
 * Loosely based on https://stackoverflow.com/a/50855078.
 */
class AuthenticateWithUserOrClientToken
{
    public function __construct(
        private readonly Authenticate $authenticate,
        private readonly EnsureClientIsResourceOwner $ensureClientIsResourceOwner
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $this->ensureClientIsResourceOwner->handle($request, $next);
        } catch (AuthenticationException) {
            return $this->authenticate->handle($request, $next, 'api');
        }
    }
}
