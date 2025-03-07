<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Contracts\ImpersonatesUsers;

class AuthController
{
    /**
     * Assuming this method is behind the `auth.cas.force` middleware, this function will ensure that the user is
     * signed in (using CAS) and then redirect them back to the original page they were going to. This function
     * largely exists as a workaround to not being able to specify what middleware is applied to Laravel Passport's
     * routes.
     */
    public function forceCasAuth(Request $request)
    {
        return redirect($request->query('next', route('home')));
    }

    public function logout(Request $request)
    {
        $request->session()->regenerate();
        cas()->logout(config('app.url'));
    }

    public function stopImpersonating(Request $request, ImpersonatesUsers $impersonator)
    {
        if ($impersonator->impersonating($request)) {
            $impersonator->stopImpersonating($request, Auth::guard(), User::class);
        }

        return redirect(route('home'));
    }
}
