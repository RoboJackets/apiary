<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AutodeskLibraryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->is_access_active) {
            return view(
                'autodesk',
                [
                    'message' => 'You have not paid dues, so you do not have access to Library.io right now.',
                ]
            );
        }

        if (0 === count($user->teams)) {
            return view(
                'autodesk',
                [
                    'message' => 'You are not a member of any teams yet. Join a team first, then try again.',
                ]
            );
        }

        if (null === $user->autodesk_email) {
            return redirect()->to('/profile');
        }

        return redirect(config('jedi.host').'/self-service/autodesk');
    }
}
