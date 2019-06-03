<?php declare(strict_types = 1);

// phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Http\Middleware;

use phpCAS;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use App\Traits\CreateOrUpdateCASUser;
use Illuminate\Http\Request;

class CASCheck
{
    use CreateOrUpdateCASUser;

    /**
     * Auth facade
     *
     * @var \Illuminate\Support\Facades\Auth
     */
    protected $auth;

    /**
     * CAS library interface
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
        phpCAS::checkAuthentication();
        if (! Auth::check()) {
            if ($this->cas->isAuthenticated()) {
                $user = $this->createOrUpdateCASUser($request);
                if (is_a($user, \App\User::class)) {
                    Auth::login($user);
                } elseif (is_a($user, 'Illuminate\Http\Response')) {
                    return $user;
                }

                return response(view(
                    'errors.generic',
                    [
                        'error_code' => 500,
                        'error_message' => 'Unknown error authenticating with CAS',
                    ]
                ), 500);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized', 401);
            }
        }
        //User is authenticated, no update needed or already updated
        return $next($request);
    }
}
