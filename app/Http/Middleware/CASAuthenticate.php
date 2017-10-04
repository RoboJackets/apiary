<?php
/**
 * Created by PhpStorm.
 * User: kberz
 * Date: 6/18/2017
 * Time: 7:34 PM
 */

namespace App\Http\Middleware;

use Log;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

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
            if (!Auth::check()) {
                //User is starting a new session, so let's update data from CAS
                $user = User::updateOrCreate(
                    [
                        'uid' => $this->cas->user()
                    ],
                    [
                        'uid' => $this->cas->user(),
                        'gtid' => $this->cas->getAttribute("gtGTID"),
                        'gt_email' => $this->cas->getAttribute("email_primary"),
                        'first_name' => $this->cas->getAttribute("givenName"),
                        'last_name' => $this->cas->getAttribute("sn")
                    ]
                );
                if ($user->wasRecentlyCreated || $user->roles->count() == 0) {
                    $role = Role::where('name', 'non-member')->first();
                    if ($role) {
                        $user->assignRole($role);
                    } else {
                        Log::error(get_class() . "Role 'non-member' not found for assignment to $user->uid.");
                    }
                }
                Auth::login($user);
            }
            //User is authenticated, no update needed or already updated
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
