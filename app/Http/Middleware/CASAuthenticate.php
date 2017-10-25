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
                $attrs = ["gtGTID", "email_primary", "givenName", "sn"];
                if ($this->cas->isMasquerading()) {
                    $masq_attrs = [];
                    foreach ($attrs as $attr) {
                        $masq_attrs[$attr] = config("cas.cas_masquerade_" . $attr);
                    }
                    $this->cas->setAttributes($masq_attrs);
                }

                foreach ($attrs as $attr) {
                    if (!$this->cas->hasAttribute($attr) || $this->cas->getAttribute($attr) == null) {
                        return response(view('errors.generic',
                            ['error_code' => 500,
                                'error_message' => 'Missing/invalid attributes from CAS']), 500);
                    }
                }

                //User is starting a new session, so let's update data from CAS
                //Sadly we can't use updateOrCreate here because of $guarded in the User model
                $user = User::where('uid', $this->cas->user())->first();
                if ($user == null) {
                    $user = new User();
                }
                $user->uid =$this->cas->user();
                $user->gtid = $this->cas->getAttribute("gtGTID");
                $user->gt_email = $this->cas->getAttribute("email_primary");
                $user->first_name = $this->cas->getAttribute("givenName");
                $user->last_name = $this->cas->getAttribute("sn");
                $user->save();

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
