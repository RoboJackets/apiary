<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\SendReminders;
use App\Util\CasUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpCAS;
use Subfission\Cas\Facades\Cas;

class CASCheck
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        phpCAS::checkAuthentication();
        if ($request->user() === null) {
            if (Cas::isAuthenticated()) {
                $user = CasUser::createOrUpdate();

                Auth::login($user);

                SendReminders::dispatch($user);

                $request->session()->put('authenticationInstant', Cas::getAttribute('authenticationDate'));
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
        }

        //User is authenticated, no update needed or already updated
        return $next($request);
    }
}
