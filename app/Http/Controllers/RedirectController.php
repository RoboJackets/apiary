<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    /**
     * Redirect to the logout route
     *
     * @return Illuminate\Http\RedirectResponse|Illuminate\Routing\Redirector
     */
    public function logout(): RedirectResponse
    {
        return redirect('logout');
    }

    public function login(): RedirectResponse
    {
        return redirect()->intended();
    }
}
