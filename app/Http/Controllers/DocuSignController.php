<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocuSignEnvelope;
use Illuminate\Http\Request;

class DocuSignController extends Controller
{
    public function signTravel(Request $request)
    {
        $user = $request->user();

        $assignment = $user->current_travel_assignment;

        if (null === $assignment) {
            return view('travel.noassignment');
        }

        if (! $assignment->needs_docusign) {
            $any_assignment_needs_docusign = $user->assignments()
                ->join('travel', 'travel.id', '=', 'travel_assignments.travel_id')
                ->needDocuSign()
                ->oldest('travel.departure_date')
                ->oldest('travel.return_date')
                ->first();
            if (null === $any_assignment_needs_docusign) {
                return view('travel.alreadysigned');
            }

            $assignment = $any_assignment_needs_docusign;
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

        if (! $user->hasSignedLatestAgreement()) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'sign the latest membership agreement',
                ]
            );
        }

        if (0 === $assignment->envelope()->count()) {
            $envelope = new DocuSignEnvelope();
            $envelope->signed_by = $user->id;
            $envelope->signable_type = $assignment->getMorphClass();
            $envelope->signable_id = $assignment->id;
            $envelope->save();

            return redirect($assignment->travel_authority_request_url);
        }

        $maybe_url = $assignment->envelope()->sole()->url;

        if (null !== $maybe_url) {
            return redirect($maybe_url);
        }

        // user can find envelope in "action required" section, theoretically, maybe
        return redirect(config('docusign.single_sign_on_url'));
    }
}
