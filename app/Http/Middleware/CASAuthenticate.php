<?php
/**
 * Created by PhpStorm.
 * User: kberz
 * Date: 6/18/2017
 * Time: 7:34 PM.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use App\Traits\CreateOrUpdateCASUser;

class CASAuthenticate
{
    use CreateOrUpdateCASUser;

    protected $auth;
    protected $cas;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->cas = app('cas');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Check to ensure the request isn't already authenticated through the API guard
        if (! Auth::guard('api')->check()) {
            // Run the user update only if they don't have an active session
            if ($this->cas->isAuthenticated() && !Auth::check()) {
                $user = $this->createOrUpdateCASUser($request);
                if (is_a($user, \App\User::class)) {
                    Auth::login($user);
                } elseif (is_a($user, "Illuminate\Http\Response")) {
                    return $user;
                } else {
                    return response(view(
                        'errors.generic',
                        [
                            'error_code' => 500,
                            'error_message' => 'Unknown error authenticating with CAS',
                        ]
                    ), 500);
                }
            } else if ($this->cas->isAuthenticated() && Auth::check()){
                //User is authenticated and already has an existing session
                return $next($request);
            } else {
                //User is not authenticated and does not have an existing session
                if ($request->ajax() || $request->wantsJson()) {
                    return response('Unauthorized', 401);
                }
                $this->cas->authenticate();
            }
        }

        //User is authenticated through the API guard (I guess? Moving this into an else() broke sessions)
        return $next($request);
    }
}
