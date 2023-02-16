<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\RetrieveIpAddressGeoLocationForSignature;
use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Http\Request;

class DocuSignController extends Controller
{
    public function signTravel(Request $request)
    {
        $user = $request->user();

        $assignment = $user->current_travel_assignment;

        if ($assignment === null) {
            return view('travel.noassignment');
        }

        if (! $assignment->needs_docusign) {
            $any_assignment_needs_docusign = $user->assignments()
                ->select("travel_assignments.*")
                ->leftJoin('travel', 'travel.id', '=', 'travel_assignments.travel_id')
                ->needDocuSign()
                ->oldest('travel.departure_date')
                ->oldest('travel.return_date')
                ->first();
            if ($any_assignment_needs_docusign === null) {
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

        if (! $user->signed_latest_agreement) {
            return view(
                'travel.actionrequired',
                [
                    'name' => $assignment->travel->name,
                    'action' => 'sign the latest membership agreement',
                ]
            );
        }

        if ($assignment->envelope()->count() === 0) {
            $envelope = new DocuSignEnvelope();
            $envelope->signed_by = $user->id;
            $envelope->signable_type = $assignment->getMorphClass();
            $envelope->signable_id = $assignment->id;
            $envelope->save();

            return redirect($assignment->travel_authority_request_url);
        }

        $maybe_url = $assignment->envelope()->sole()->url;

        if ($maybe_url !== null) {
            return redirect($maybe_url);
        }

        // user can find envelope in "action required" section, theoretically, maybe
        return redirect(config('docusign.single_sign_on_url'));
    }

    public function signAgreement(Request $request)
    {
        $user = $request->user();

        if ($user->signed_latest_agreement) {
            return view('agreement.alreadysigned');
        }

        $template = MembershipAgreementTemplate::orderByDesc('updated_at')->firstOrFail();

        $signature = Signature::firstOrNew(
            [
                'membership_agreement_template_id' => $template->id,
                'user_id' => $user->id,
                'electronic' => true,
                'complete' => false,
            ]
        );

        $ip = $request->ip();

        if ($ip === null) { // I have no idea what could possibly cause this, but that's what the contract says
            return view(
                'agreement.error',
                [
                    'message' => 'We could not detect your IP address.',
                ]
            );
        }

        $signature->ip_address = $ip;
        $signature->user_agent = $request->header('User-Agent');
        $signature->save();

        RetrieveIpAddressGeoLocationForSignature::dispatch($signature);

        if ($signature->envelope()->count() === 0) {
            $envelope = new DocuSignEnvelope();
            $envelope->signed_by = $user->id;
            $envelope->signable_type = $signature->getMorphClass();
            $envelope->signable_id = $signature->id;
            $envelope->save();

            return redirect(self::generateAgreementPowerFormUrl($user));
        }

        $maybe_url = $signature->envelope()->sole()->url;

        if ($maybe_url !== null) {
            return redirect($maybe_url);
        }

        // user can find envelope in "action required" section, theoretically, maybe
        return redirect(config('docusign.single_sign_on_url'));
    }

    private static function generateAgreementPowerFormUrl(User $user): string
    {
        return config('docusign.membership_agreement.powerform_url').'&'.http_build_query(
            [
                config(
                    'docusign.membership_agreement.member_name'
                ).'_UserName' => $user->full_name,

                config(
                    'docusign.membership_agreement.member_name'
                ).'_Email' => $user->uid.'@gatech.edu',

                config(
                    'docusign.membership_agreement.ingest_mailbox_name'
                ).'_UserName' => config('app.name'),

                config(
                    'docusign.membership_agreement.ingest_mailbox_name'
                ).'_Email' => config('docusign.ingest_mailbox'),

                config(
                    'docusign.membership_agreement.archive_mailbox_name'
                ).'_UserName' => 'Membership Agreement Archives',

                config(
                    'docusign.membership_agreement.archive_mailbox_name'
                ).'_Email' => config('services.membership_agreement_archive_email'),
            ]
        );
    }
}
