<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Assuming this method is behind the `auth.cas.force` middleware, this function will ensure that the user is
     * signed in (using CAS) and then redirect them back to the original page they were going to. This function
     * largely exists as a workaround to not being able to specify what middleware is applied to Laravel Passport's
     * routes.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function forceCasAuth(Request $request)
    {
        $request->session()->setPreviousUrl($request->query('next', route('home')));
        return redirect($request->query('next', route('home')));
    }

    public function logout(Request $request)
    {
        $request->session()->regenerate();
        cas()->logout(config('app.url'));
    }
}
