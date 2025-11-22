<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SponsorAuthCheck
{
    public function handle($request, Closure $next)
    {
        if (!Auth::guard('sponsor')->check()) {
            return redirect('/sponsor/login');
        }

        if (Auth::user() instanceof \App\Models\User) {
            return redirect('/sponsor/login');
        }

        return $next($request);
    }
}