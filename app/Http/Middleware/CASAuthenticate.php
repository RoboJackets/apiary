<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed

namespace App\Http\Middleware;

use App\Traits\CreateOrUpdateCASUser;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RoboJackets\AuthStickler;

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
        'authn_method',
        'eduPersonPrimaryAffiliation',
    ];

    /**
     * List of array attributes that may be set during masquerade.
     *
     * @var array<string>
     *
     * @phan-read-only
     */
    private static $arrayAttrs = [
        'gtAccountEntitlement',
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
            if ($this->cas->isAuthenticated() && null === $request->user()) {
                if ($this->cas->isMasquerading()) {
                    $masq_attrs = [];
                    foreach (self::$attrs as $attr) {
                        $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
                    }
                    // Split the attributes that we need to split
                    foreach (self::$arrayAttrs as $attr) {
                        $masq_attrs[$attr] = explode(',', $masq_attrs[$attr]);
                    }
                    $this->cas->setAttributes($masq_attrs);
                }

                AuthStickler::check($this->cas);

                $user = $this->createOrUpdateCASUser();
                Auth::login($user);
            }

            if ($this->cas->isAuthenticated() && null !== $request->user()) {
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
