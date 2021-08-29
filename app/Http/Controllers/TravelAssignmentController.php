<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class TravelAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (0 === $user->assignments()->count()) {
            return view('travel.noassignment');
        }

        if (
            0 === $user
                ->assignments()
                ->leftJoin('travel', static function (JoinClause $join): void {
                    $join->on('travel.id', '=', 'travel_assignments.travel_id');
                })
                ->where('return_date', '>', date('Y-m-d'))
                ->count()
        ) {
            return view('travel.noassignment');
        }

        $assignment = $user->assignments()->orderByDesc('travel_assignments.id')->first();

        if (! $user->is_active) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'pay dues',
                ]
            );
        }

        if (! $user->hasSignedLatestAgreement()) {
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
                'documents_received' => $assignment->documents_received,
                'paid' => $assignment->is_paid,
            ]
        );
    }
}
