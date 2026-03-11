<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware\SponsorAuthenticate;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Symfony\Component\HttpFoundation\Response;

/**
 * Try to authenticate an incoming request either as a sponsor or using a client token.
 *
 * Loosely based on https://stackoverflow.com/a/50855078.
 */
class AuthenticateSponsorOrClientToken
{
    public function __construct(
        private readonly EnsureClientIsResourceOwner $ensureClientIsResourceOwner,
        private readonly SponsorAuthenticate $sponsorAuthenticate
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
            return $this->sponsorAuthenticate->handle($request, $next, false);
        }
    }
}
