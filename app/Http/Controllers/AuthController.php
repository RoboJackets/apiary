<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class AuthController extends Controller
{
    /**
     * Assuming this method is behind the `auth.cas.force` middleware, this function will ensure that the user is
     * signed in (using CAS) and then redirect them back to the original page they were going to. This function
     * largely exists as a workaround to not being able to specify what middleware is applied to Laravel Passport's
     * routes.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function forceCasAuth() {
        return redirect(url()->previous());
    }

    public function logout(Request $request)
    {
        $request->session()->regenerate();
        cas()->logout(config('app.url'));
    }
}
