<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;

class TravelAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $assignment = $user->current_travel_assignment;

        if ($assignment === null) {
            return view('travel.noassignment');
        }

        if (! $user->is_active) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'pay dues',
                ]
            );
        }

        if (! $user->signed_latest_agreement) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'sign the latest membership agreement',
                ]
            );
        }

        return view(
            'travel.index',
            [
                'travel' => $assignment->travel,
                'tar_received' => $assignment->tar_received,
                'paid' => $assignment->is_paid,
                'has_emergency_contact' => $user->has_emergency_contact_information,
            ]
        );
    }
}
