<?php
/**
 * Created by PhpStorm.
 * User: kberz
 * Date: 6/18/2017
 * Time: 7:34 PM
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class CASAuthenticate
{
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
        if ($this->cas->isAuthenticated()) {
            // Store the user credentials in a Laravel managed session
            session()->put('cas_user', $this->cas->user());
        } else {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
            $this->cas->authenticate();
        }
        return $next($request);
    }
}
