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
            } else {
                if ($request->ajax() || $request->wantsJson()) {
                    return response('Unauthorized', 401);
                }
                $this->cas->authenticate();
            }
        }
        //User is authenticated, no update needed or already updated
        return $next($request);
    }
}
