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
                        'error_code' => 400,
                        'error_message' => 'You already have a GitHub account linked.',
                    ]
                ),
                400
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
                        'error_code' => 400,
                        'error_message' => 'You already have a GitHub account linked.',
                    ]
                ),
                400
            );
        }

        $localUser->github_username = Socialite::driver('github')->user()->getNickname();

        $localUser->save(); // this will trigger a JEDI sync

        if ($localUser->is_access_active && 0 !== count($localUser->teams)) {
            return redirect(config('jedi.host').'/self-service/github');
        }

        alert()->success('Your GitHub account was successfully linked.', 'Success!');

        return redirect('/profile');
    }
}
