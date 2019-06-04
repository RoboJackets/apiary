<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Http\Middleware;

use phpCAS;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use App\Traits\CreateOrUpdateCASUser;

class CASCheck
{
    use CreateOrUpdateCASUser;

    /**
     * Auth facade.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * CAS library interface.
     *
     * @var \Subfission\Cas\CasManager
     */
    protected $cas;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->cas = app('cas');
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        phpCAS::checkAuthentication();
        if (! Auth::check()) {
            if ($this->cas->isAuthenticated()) {
                $user = $this->createOrUpdateCASUser($request);
                Auth::login($user);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
        }
        //User is authenticated, no update needed or already updated
        return $next($request);
    }
}
