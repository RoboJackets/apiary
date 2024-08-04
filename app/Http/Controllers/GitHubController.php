<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirectToProvider(Request $request)
    {
        if ($request->user()->github_username !== null) {
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

        if ($localUser->github_username !== null) {
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

        if ($request->error === 'access_denied') {
            return response(
                view(
                    'errors.generic',
                    [
                        'error_code' => 400,
                        'error_message' => 'You canceled connecting MyRoboJackets to your GitHub account. This is '
                            .'required for us to verify your GitHub identity.',
                    ]
                ),
                400
            );
        }

        $localUser->github_username = Socialite::driver('github')->user()->getNickname();

        $localUser->save(); // this will trigger a JEDI sync

        if ($localUser->is_access_active && count($localUser->teams) !== 0) {
            return redirect(config('jedi.host').'/self-service/github');
        }

        alert()->success('Your GitHub account was successfully linked.', 'Success!');

        return redirect('/profile');
    }
}
