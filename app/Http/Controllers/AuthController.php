<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class RedirectController extends Controller
{
    public function logout() {
        Session::flush();
        cas()->logout(config('app.url'))
    }
}
