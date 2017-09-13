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
use App\User;
use Illuminate\Support\Facades\Auth;

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
            $user = User::where('uid', '=', $this->cas->user())->first();
            if (!$user || $user == null) {
                $user = new User();
                $user->uid = $this->cas->user();
                $user->gtid = $this->cas->getAttribute("gtGTID");
                $user->gt_email = $this->cas->getAttribute("email_primary");
                $user->first_name = $this->cas->getAttribute("givenName");
                $user->last_name = $this->cas->getAttribute("sn");
                $user->save();
            }
            Auth::login($user);
            return $next($request);
        } else {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
            $this->cas->authenticate();
        }
        return $next($request);
    }
}
