<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClickUpController
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->is_access_active) {
            return view(
                'clickup',
                [
                    'message' => 'You have not paid dues, so you do not have access to ClickUp right now.',
                ]
            );
        }

        if (count($user->teams) === 0) {
            return view(
                'clickup',
                [
                    'message' => 'You are not a member of any teams yet. Join a team first, then try again.',
                ]
            );
        }

        if ($user->clickup_email === null) {
            return redirect()->to('/profile');
        }

        return redirect(config('jedi.host').'/self-service/clickup');
    }
}
