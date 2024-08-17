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
                'needs_profile_information' => ! $user->has_emergency_contact_information ||
                    ($assignment->travel->needs_airfare_form &&
                        ($user->legal_gender === null || $user->date_of_birth === null)
                    ),
                'needs_agreement' => ! $user->signed_latest_agreement,
                'needs_dues' => ! $user->is_active,
                'tar_received' => $assignment->tar_received,
                'paid' => $assignment->is_paid,
            ]
        );
    }
}
