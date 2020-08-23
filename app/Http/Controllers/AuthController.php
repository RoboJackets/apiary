<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function logout(Request $request)
    {
        $request->session()->regenerate();
        cas()->logout(config('app.url'));
    }
}
