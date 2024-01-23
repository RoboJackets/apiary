<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\SendReminders;
use App\Util\CasUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Subfission\Cas\Facades\Cas;

class CASAuthenticate
{
    /**
     * List of attributes that may be set during masquerade.
     *
     * @var array<string>
     *
     * @phan-read-only
     */
    private static $attrs = [
        'gtGTID',
        'email_primary',
        'givenName',
        'sn',
        'authnContextClass',
        'eduPersonPrimaryAffiliation',
        'eduPersonScopedAffiliation',
        'authenticationDate',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        //Check to ensure the request isn't already authenticated through the API guard
        if (! Auth::guard('api')->check()) {
            // Run the user update only if they don't have an active session
            if (Cas::isAuthenticated() && $request->user() === null) {
                if (Cas::isMasquerading()) {
                    $masq_attrs = [];
                    foreach (self::$attrs as $attr) {
                        $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
                    }
                    Cas::setAttributes($masq_attrs);
                }

                if (
                    config('features.sandbox-mode') === true &&
                    ! in_array(Cas::user(), config('features.sandbox-users'), true)
                ) {
                    abort(403);
                }

                $user = CasUser::createOrUpdate();

                if (
                    config('features.sandbox-mode') === true &&
                    in_array(Cas::user(), config('features.sandbox-users'), true)
                ) {
                    $user->syncRoles(['admin']);
                }

                Auth::login($user);

                SendReminders::dispatch($user);

                $request->session()->put('authenticationInstant', Cas::getAttribute('authenticationDate'));
            }

            if (Cas::isAuthenticated() && $request->user() !== null) {
                //User is authenticated and already has an existing session
                return $next($request);
            }

            //User is not authenticated and does not have an existing session
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
            Cas::authenticate();
        }

        //User is authenticated through the API guard (I guess? Moving this into an else() broke sessions)
        return $next($request);
    }
}
