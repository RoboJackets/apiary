<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class TravelAssignmentController extends Controller
{
    public function index(Request $request)
    {
        if (0 === $request->user()->assignments()->count()) {
            return view('travel.noassignment');
        }

        if (
            0 === $request->user()
                ->assignments()
                ->leftJoin('travel', static function (JoinClause $join): void {
                    $join->on('travel.id', '=', 'travel_assignments.travel_id');
                })
                ->where('return_date', '>', date('Y-m-d'))
                ->count()
        ) {
            return view('travel.noassignment');
        }

        $assignment = $request->user()->assignments()->orderByDesc('travel_assignments.id')->first();

        return view(
            'travel.index',
            [
                'travel' => $assignment->travel,
                'documents_received' => $assignment->documents_received,
                'paid' => $assignment->is_paid,
                'url' => $request->fullUrl(),
            ]
        );
    }
}
