<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Traits\CreateOrUpdateCASUser;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RoboJackets\AuthStickler;
use RoboJackets\ErrorPages\Unauthorized;

class CASAuthenticate
{
    use CreateOrUpdateCASUser;

    /**
     * Auth facade.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     *
     * @phan-read-only
     */
    protected $auth;

    /**
     * CAS library interface.
     *
     * @var \Subfission\Cas\CasManager
     *
     * @phan-read-only
     */
    protected $cas;

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
    ];

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
        //Check to ensure the request isn't already authenticated through the API guard
        if (! Auth::guard('api')->check()) {
            // Run the user update only if they don't have an active session
            if ($this->cas->isAuthenticated() && $request->user() === null) {
                if ($this->cas->isMasquerading()) {
                    $masq_attrs = [];
                    foreach (self::$attrs as $attr) {
                        $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
                    }
                    $this->cas->setAttributes($masq_attrs);
                }

                if (config('features.demo-mode') === null) {
                    AuthStickler::check($this->cas);
                } else {
                    if ($this->cas->user() !== config('features.demo-mode')) {
                        Unauthorized::render(0);
                        exit;
                    }
                }

                $user = $this->createOrUpdateCASUser();

                if ($this->cas->user() === config('features.demo-mode')) {
                    $user->syncRoles(['admin']);
                }

                Auth::login($user);
            }

            if ($this->cas->isAuthenticated() && $request->user() !== null) {
                //User is authenticated and already has an existing session
                return $next($request);
            }

            //User is not authenticated and does not have an existing session
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
            $this->cas->authenticate();
        }

        //User is authenticated through the API guard (I guess? Moving this into an else() broke sessions)
        return $next($request);
    }
}
