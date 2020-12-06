<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToProvider(Request $request)
    {
        if (null !== $request->user()->gmail_address) {
            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 400,
                        'error_message' => 'You already have a Google account linked.',
                    ]
                ),
                400
            );
        }

        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        $localUser = $request->user();

        if (null !== $localUser->gmail_address) {
            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 400,
                        'error_message' => 'You already have a Google account linked.',
                    ]
                ),
                400
            );
        }

        $localUser->gmail_address = Socialite::driver('google')->user()->getEmail();

        $localUser->save();

        alert()->success('Your Google account was successfully linked.', 'Success!');

        return redirect('/profile');
    }
}
