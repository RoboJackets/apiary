<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TravelAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $assignment = $user->current_travel_assignment;

        if ($assignment === null) {
            return view('travel.noassignment');
        }

        return view(
            'travel.index',
            [
                'travel' => $assignment->travel,
                'needs_agreement' => ! $user->signed_latest_agreement,
                'needs_dues' => ! $user->is_active,
                'tar_received' => $assignment->tar_received,
                'paid' => $assignment->is_paid,
                'has_emergency_contact' => $user->has_emergency_contact_information,
            ]
        );
    }
}
