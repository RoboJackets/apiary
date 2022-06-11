<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocuSignEnvelope;
use App\Models\TravelAssignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DocuSignController extends Controller
{
    public function signTravel(Request $request)
    {
        $user = $request->user();

        // this is still a little wonky but rolling with it for right now
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

        $assignmentWithNoEnvelope = TravelAssignment::doesntHave('envelope')
            ->where('user_id', $user->id)
            ->oldest('updated_at')
            ->first();

        $assignmentWithIncompleteEnvelope = TravelAssignment::where('user_id', $user->id)
            ->whereHas('envelope', static function (Builder $q): void {
                $q->where('complete', false);
            })
            ->oldest('updated_at')
            ->first();

        if (null !== $assignmentWithIncompleteEnvelope) {
            $assignment = $assignmentWithIncompleteEnvelope;

            $envelope = $assignmentWithIncompleteEnvelope->envelope[0];
        } elseif (null !== $assignmentWithNoEnvelope) {
            $assignment = $assignmentWithNoEnvelope;

            $envelope = new DocuSignEnvelope();
            $envelope->signed_by = $user->id;
            $envelope->signable_type = $assignmentWithNoEnvelope->getMorphClass();
            $envelope->signable_id = $assignmentWithNoEnvelope->id;
            $envelope->save();
        } else {
            return view('travel.alreadysigned');
        }

        if ($envelope->wasRecentlyCreated()) {
            return redirect($assignment->travel_authority_request_url);
        }

        if (null !== $envelope->url) {
            return redirect($envelope->url);
        }

        // user can find envelope in "action required" section
        return redirect(config('docusign.single_sign_on_url'));
    }
}
