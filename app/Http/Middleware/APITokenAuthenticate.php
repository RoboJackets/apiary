<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;

class APITokenAuthenticate
{
    protected $auth;

    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = Auth::guard('api')->getTokenForRequest();
        $hasToken = Auth::guard('api')->validate(['api_token' => $token]);
        if ($hasToken) {
            $this->auth->shouldUse('api');
            $this->auth->authenticate();
        }

        return $next($request);
    }
}
