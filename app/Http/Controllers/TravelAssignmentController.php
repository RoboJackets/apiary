<?php

declare(strict_types=1);

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingAfter

namespace App\Http\Controllers;

use App\Models\TravelAssignment;
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
                'tar_received' => $assignment->tar_received,
                'tar_url' => self::generateTravelAuthorityRequestUrl($assignment),
                'paid' => $assignment->is_paid,
            ]
        );
    }

    private static function generateTravelAuthorityRequestUrl(TravelAssignment $assignment): string
    {
        return config('docusign.travel_authority_request.powerform_url').'&'.http_build_query(
            [
                config('docusign.travel_authority_request.fields.state_contract_airline')
                    => $assignment->travel->tar_transportation_mode['state_contract_airline'] ? 'x' : 0,
                config('docusign.travel_authority_request.fields.non_contract_airline')
                    => $assignment->travel->tar_transportation_mode['non_contract_airline'] ? 'x' : 0,
                config('docusign.travel_authority_request.fields.personal_automobile')
                    => $assignment->travel->tar_transportation_mode['personal_automobile'] ? 'x' : 0,
                config('docusign.travel_authority_request.fields.rental_vehicle')
                    => $assignment->travel->tar_transportation_mode['rental_vehicle'] ? 'x' : 0,
                config('docusign.travel_authority_request.fields.other')
                    => $assignment->travel->tar_transportation_mode['other'] ? 'x' : 0,
                config('docusign.travel_authority_request.fields.itinerary') => $assignment->travel->tar_itinerary,
                config('docusign.travel_authority_request.fields.purpose') => $assignment->travel->tar_purpose,
                config('docusign.travel_authority_request.fields.airfare_cost') => $assignment->travel->tar_airfare,
                config('docusign.travel_authority_request.fields.other_cost') => $assignment->travel->tar_other_trans,
                config('docusign.travel_authority_request.fields.mileage_cost') => $assignment->travel->tar_mileage,
                config('docusign.travel_authority_request.fields.lodging_cost') => $assignment->travel->tar_lodging,
                config('docusign.travel_authority_request.fields.registration_cost')
                    => $assignment->travel->tar_registration,
                config('docusign.travel_authority_request.fields.total_cost') => (
                    $assignment->travel->tar_airfare +
                    $assignment->travel->tar_other_trans +
                    $assignment->travel->tar_mileage +
                    $assignment->travel->tar_lodging +
                    $assignment->travel->tar_registration
                ),
                config('docusign.travel_authority_request.fields.departure_date')
                    => $assignment->travel->departure_date->toDateString(),
                config('docusign.travel_authority_request.fields.return_date')
                    => $assignment->travel->return_date->toDateString(),
                config('docusign.travel_authority_request.traveler_name').'_UserName'
                    => $assignment->user->name,
                config('docusign.travel_authority_request.traveler_name').'_Email'
                    => $assignment->user->uid.'@gatech.edu',
                config('docusign.travel_authority_request.treasurer_name').'_UserName'
                    => config('docusign.treasurer_name'),
                config('docusign.travel_authority_request.treasurer_name').'_Email'
                    => config('docusign.treasurer_account'),
            ]
        );
    }
}
