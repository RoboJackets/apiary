<?php

declare(strict_types=1);

use Illuminate\Http\RedirectResponse;

namespace App\Http\Controllers;

class RedirectController extends Controller
{
    public function logout(): RedirectResponse {
        return redirect('logout');
    }

    public function login(): RedirectResponse {
        return redirect()->intended();
    }
}
