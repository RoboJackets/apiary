<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use App\Traits\CreateOrUpdateCASUser;
use RoboJackets\ErrorPages\BadNetwork;
use RoboJackets\ErrorPages\DuoNotEnabled;
use RoboJackets\ErrorPages\DuoOutage;
use RoboJackets\ErrorPages\EduroamISSDisabled;
use RoboJackets\ErrorPages\EduroamNonGatech;
use RoboJackets\ErrorPages\SystemError;
use RoboJackets\ErrorPages\Unauthorized;
use RoboJackets\ErrorPages\UsernameContainsDomain;
use RoboJackets\NetworkCheck;

class CASAuthenticate
{
    use CreateOrUpdateCASUser;

    /**
     * Auth facade.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * CAS library interface.
     *
     * @var \Subfission\Cas\CasManager
     */
    protected $cas;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->cas = app('cas');
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //Check to ensure the request isn't already authenticated through the API guard
        if (! Auth::guard('api')->check()) {
            // Run the user update only if they don't have an active session
            if ($this->cas->isAuthenticated() && null === $request->user()) {
                $username = strtolower($this->cas->user());

                if (false !== strpos($username, '@')) {
                    foreach (array_keys($_COOKIE) as $key) {
                        setcookie($key, '', time() - 3600);
                    }
                    UsernameContainsDomain::render($username);
                    exit;
                }

                $attrs = ['gtGTID', 'email_primary', 'givenName', 'sn', 'authn_method'];
                // Attributes that will be split by commas when masquerading
                $arrayAttrs = ['gtPersonEntitlement', 'gtAccountEntitlement'];
                // Merge them together so we verify all attributes are present, even the array ones
                $attrs = array_merge($attrs, $arrayAttrs);
                if ($this->cas->isMasquerading()) {
                    $masq_attrs = [];
                    foreach ($attrs as $attr) {
                        $masq_attrs[$attr] = config('cas.cas_masquerade_'.$attr);
                    }
                    // Split the attributes that we need to split
                    foreach ($arrayAttrs as $attr) {
                        $masq_attrs[$attr] = explode(',', $masq_attrs[$attr]);
                    }
                    $this->cas->setAttributes($masq_attrs);
                }

                if ('duo-two-factor' !== $this->cas->getAttribute('authn_method')) {
                    if (in_array(
                        '/gt/central/services/iam/two-factor/duo-user',
                        $this->cas->getAttribute('gtAccountEntitlement')
                    )
                    ) {
                        DuoOutage::render();
                        exit;
                    }
                    DuoNotEnabled::render();
                    exit;
                }

                $network = NetworkCheck::detect();
                if (NetworkCheck::EDUROAM_ISS_DISABLED === $network) {
                    EduroamISSDisabled::render();
                    exit;
                }
                if (NetworkCheck::GTOTHER === $network) {
                    BadNetwork::render('GTother', $username, phpCAS::getAttribute('eduPersonPrimaryAffiliation'));
                    exit;
                }
                if (NetworkCheck::GTVISITOR === $network) {
                    BadNetwork::render('GTvisitor', $username, phpCAS::getAttribute('eduPersonPrimaryAffiliation'));
                    exit;
                }
                if (NetworkCheck::EDUROAM_NON_GATECH_V4 === $network || NetworkCheck::EDUROAM_NON_GATECH_V6 === $network) {
                    EduroamNonGatech::render($username, phpCAS::getAttribute('eduPersonPrimaryAffiliation'));
                    exit;
                }

                $user = $this->createOrUpdateCASUser($request);
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
