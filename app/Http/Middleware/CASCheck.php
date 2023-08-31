<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\SendReminders;
use App\Traits\CreateOrUpdateCASUser;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpCAS;

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
     */
    public function handle(Request $request, Closure $next)
    {
        phpCAS::checkAuthentication();
        if ($request->user() === null) {
            if ($this->cas->isAuthenticated()) {
                $user = $this->createOrUpdateCASUser();

                Auth::login($user);

                SendReminders::dispatch($user);

                $request->session()->put('authenticationInstant', $this->cas->getAttribute('authenticationDate'));
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
        }

        //User is authenticated, no update needed or already updated
        return $next($request);
    }
}
