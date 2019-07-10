<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirectToProvider(Request $request)
    {
        if (null !== $request->user()->github_username) {
            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => ,
                        'error_message' => 'You already have a GitHub account linked.',
                    ]
                ),
                
            );
        }

        return Socialite::driver('github')->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        $localUser = $request->user();

        if (null !== $localUser->github_username) {
            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => ,
                        'error_message' => 'You already have a GitHub account linked.',
                    ]
                ),
                
            );
        }

        $localUser->github_username = Socialite::driver('github')->user()->getNickname();

        $localUser->save();

        alert()->success('Your GitHub account was successfully linked.', 'Success!');

        return redirect('/profile');
    }
}
