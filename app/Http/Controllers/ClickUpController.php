<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClickUpController extends Controller
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

        if (0 === count($user->teams)) {
            return view(
                'clickup',
                [
                    'message' => 'You are not a member of any teams yet. Join a team first, then try again.',
                ]
            );
        }

        if (null === $user->clickup_email) {
            return redirect()->to('/profile');
        }

        return redirect(config('jedi.host').'/self-service/clickup');
    }
}
