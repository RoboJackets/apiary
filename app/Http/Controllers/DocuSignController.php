<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion

namespace App\Http\Controllers;

use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Util\DocuSign;
use Carbon\Carbon;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Model\ConnectEventData;
use DocuSign\eSign\Model\EmailSettings;
use DocuSign\eSign\Model\EnvelopeDefinition;
use DocuSign\eSign\Model\EventNotification;
use DocuSign\eSign\Model\Expirations;
use DocuSign\eSign\Model\Notification;
use DocuSign\eSign\Model\RecipientEmailNotification;
use DocuSign\eSign\Model\RecipientViewRequest;
use DocuSign\eSign\Model\Reminders;
use DocuSign\eSign\Model\TemplateRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

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
                ->select('travel_assignments.*')
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

    /**
     * Redirect to a DocuSign signing session for the membership agreement.
     *
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function signAgreement(Request $request)
    {
        $user = $request->user();

        if ($user->signed_latest_agreement) {
            return view('agreement.alreadysigned');
        }

        $template = MembershipAgreementTemplate::orderByDesc('updated_at')->firstOrFail();

        $recipientApiClient = DocuSign::getApiClientForUser($user);

        if ($recipientApiClient === null) {
            $state = DocuSign::getState();

            $docusign = DocuSign::getApiClient(withAccessToken: false);

            $authorization_uri = $docusign->getAuthorizationURI(
                client_id: config('docusign.client_id'),
                scopes: [ApiClient::$SCOPE_SIGNATURE],
                redirect_uri: route('docusign.auth.complete'),
                response_type: 'code',
                state: $state
            ).'&prompt=login&login_hint='.$user->uid.'@gatech.edu';

            $request->session()->put('state', $state);
            $request->session()->put('next', 'signAgreement');

            return redirect($authorization_uri);
        }

        $signature = Signature::firstOrCreate(
            [
                'membership_agreement_template_id' => $template->id,
                'user_id' => $user->id,
                'electronic' => true,
                'complete' => false,
            ]
        );

        $envelopesApi = new EnvelopesApi(DocuSign::getApiClient());

        if ($signature->envelope()->whereNotNull('envelope_id')->count() === 0) {
            $envelope = new DocuSignEnvelope();
            $envelope->signed_by = $user->id;
            $envelope->signable_type = $signature->getMorphClass();
            $envelope->signable_id = $signature->id;
            $envelope->save();

            $envelopeResponse = $envelopesApi->createEnvelope(
                account_id: config('docusign.account_id'),
                envelope_definition: (new EnvelopeDefinition())
                    ->setStatus('sent')
                    ->setTemplateId(config('docusign.membership_agreement_template_id'))
                    ->setTemplateRoles(
                        [
                            (new TemplateRole())
                                ->setEmail($user->uid.'@gatech.edu')
                                ->setName($user->full_name)
                                ->setRoleName('Member')
                                ->setEmailNotification(
                                    (new RecipientEmailNotification())
                                        ->setEmailSubject('RoboJackets Membership Agreement')
                                        ->setEmailBody(
                                            trim(view('mail.agreement.docusignenvelopenotification')->render())
                                        )
                                        ->setSupportedLanguage('en')
                                ),
                        ]
                    )
                    ->setEmailSubject('RoboJackets Membership Agreement for '.$user->full_name)
                    ->setEmailBlurb(trim(view('mail.agreement.docusignenvelopenotification')->render()))
                    ->setEmailSettings(
                        (new EmailSettings())
                            ->setReplyEmailAddressOverride('support@robojackets.org')
                            ->setReplyEmailNameOverride('RoboJackets')
                    )->setNotification(
                        (new Notification())
                            ->setUseAccountDefaults(false)
                            ->setReminders(
                                (new Reminders())
                                    ->setReminderEnabled(true)
                                    ->setReminderDelay(2)
                                    ->setReminderFrequency(2)
                            )
                            ->setExpirations(
                                (new Expirations())
                                    ->setExpireEnabled(true)
                                    ->setExpireWarn(10)
                                    ->setExpireAfter(60)
                            )
                    )
                    ->setAllowComments(false)
                    ->setAllowMarkup(false)
                    ->setAllowReassign(false)
                    ->setAllowRecipientRecursion(false)
                    ->setAllowViewHistory(true)
                    ->setAutoNavigation(false)
                    ->setEnableWetSign(true)
                    ->setEnvelopeIdStamping(true)
                    ->setEventNotifications(
                        [
                            (new EventNotification())
                                ->setEventData(
                                    (new ConnectEventData())
                                        ->setVersion('restv2.1')
                                        ->setIncludeData(
                                            [
                                                'recipients',
                                            ]
                                        )
                                )
                                ->setDeliveryMode('SIM')
                                ->setEvents(
                                    [
                                        'envelope-created',
                                        'envelope-sent',
                                        'envelope-resent',
                                        'envelope-delivered',
                                        'envelope-completed',
                                        'envelope-declined',
                                        'envelope-voided',
                                        'recipient-authenticationfailed',
                                        'recipient-autoresponded',
                                        'recipient-declined',
                                        'recipient-delivered',
                                        'recipient-completed',
                                        'recipient-sent',
                                        'recipient-resent',
                                        'template-created',
                                        'template-modified',
                                        'template-deleted',
                                        'envelope-corrected',
                                        'envelope-purge',
                                        'envelope-deleted',
                                        'envelope-discard',
                                        'recipient-reassign',
                                        'recipient-delegate',
                                        'recipient-finish-later',
                                        'click-agreed',
                                        'click-declined',
                                    ]
                                )
                                ->setIncludeEnvelopeVoidReason(false)
                                ->setLoggingEnabled(true)
                                ->setRequireAcknowledgment(true)
                                ->setUrl(
                                    URL::signedRoute('webhook-client-docusign', ['internalEnvelopeId' => $envelope->id])
                                ),
                            (new EventNotification())
                                ->setEventData(
                                    (new ConnectEventData())
                                        ->setVersion('restv2.1')
                                        ->setIncludeData(
                                            [
                                                'recipients',
                                                'documents',
                                            ]
                                        )
                                )
                                ->setDeliveryMode('SIM')
                                ->setEvents(
                                    [
                                        'envelope-completed',
                                    ]
                                )
                                ->setIncludeEnvelopeVoidReason(false)
                                ->setLoggingEnabled(true)
                                ->setRequireAcknowledgment(true)
                                ->setUrl(
                                    URL::signedRoute('webhook-client-docusign', ['internalEnvelopeId' => $envelope->id])
                                ),
                        ]
                    )
                    ->setUseDisclosure(true)
            );

            $envelope->envelope_id = $envelopeResponse->getEnvelopeId();
            $envelope->save();
        } else {
            $envelope = $signature->envelope()->whereNotNull('envelope_id')->sole();
        }

        $recipientViewResponse = (new EnvelopesApi($recipientApiClient))->createRecipientView(
            account_id: config('docusign.account_id'),
            envelope_id: $envelope->envelope_id,
            recipient_view_request: (new RecipientViewRequest())
                ->setAuthenticationInstant($request->session()->get('authenticationInstant'))
                ->setAuthenticationMethod('SingleSignOn_Other')
                ->setEmail($user->uid.'@gatech.edu')
                ->setUserName($user->full_name)
                ->setReturnUrl(
                    URL::signedRoute(
                        'docusign.complete',
                        [
                            'envelope_id' => $envelope->envelope_id,
                        ]
                    )
                )
                ->setSecurityDomain(config('cas.cas_hostname'))
                ->setXFrameOptions('deny')
        );

        return redirect($recipientViewResponse->getUrl());
    }

    public function redirectToProvider(Request $request)
    {
        $state = DocuSign::getState();

        $docusign = DocuSign::getApiClient(withAccessToken: false);

        $authorization_uri = $docusign->getAuthorizationURI(
            client_id: config('docusign.client_id'),
            scopes: [ApiClient::$SCOPE_SIGNATURE, ApiClient::$SCOPE_IMPERSONATION],
            redirect_uri: route('docusign.auth.complete'),
            response_type: 'code',
            state: $state
        ).'&prompt=login';

        $request->session()->put('state', $state);
        $request->session()->put('next', 'getGlobalToken');

        return redirect($authorization_uri);
    }

    /**
     * Handle the OAuth consent response from DocuSign.
     *
     * @phan-suppress PhanPluginInconsistentReturnMethod
     * @phan-suppress PhanTypeMismatchArgumentProbablyReal
     */
    public function handleProviderCallback(Request $request)
    {
        if ($request->error === 'access_denied') {
            return redirect('/');
        }

        if (
            ! $request->has('code') ||
            ! $request->has('state') ||
            $request->session()->get('state') !== $request->state
        ) {
            abort(500);
        }

        $docusign = DocuSign::getApiClient(withAccessToken: false);

        /** @var \DocuSign\eSign\Client\Auth\OAuthToken $tokens */
        $tokens = $docusign->generateAccessToken(
            client_id: config('docusign.client_id'),
            client_secret: config('docusign.client_secret'),
            code: $request->code
        )[0];

        /** @var \DocuSign\eSign\Client\Auth\UserInfo $userinfo */
        $userinfo = $docusign->getUserInfo($tokens->getAccessToken())[0];

        $userInSameAccount = false;

        /** @var \DocuSign\eSign\Client\Auth\Account $account */
        foreach ($userinfo->getAccounts() as $account) {
            if ($account->getAccountId() === config('docusign.account_id')) {
                $userInSameAccount = true;
            }
        }

        if (! $userInSameAccount) {
            $userinfo_serialized = DocuSign::serializeUserInfo($userinfo);

            Log::info($userinfo_serialized);

            abort(401);
        }

        switch ($request->session()->get('next')) {
            case 'getGlobalToken':
                $userinfo_serialized = DocuSign::serializeUserInfo($userinfo);

                Log::info($userinfo_serialized);

                DocuSign::getApiClient();

                return response()->json($userinfo_serialized);
            case 'signAgreement':
                /** @var \App\Models\User $user */
                $user = $request->user();

                if ($userinfo->getEmail() !== $user->uid.'@gatech.edu') {
                    $userinfo_serialized = DocuSign::serializeUserInfo($userinfo);

                    Log::info($userinfo_serialized);

                    abort(401);
                }

                $user->docusign_access_token = $tokens->getAccessToken();
                $user->docusign_access_token_expires_at = Carbon::now()->addSeconds($tokens->getExpiresIn() - 60);
                $user->docusign_refresh_token = $tokens->getRefreshToken();
                $user->docusign_refresh_token_expires_at = Carbon::now()->addDays(29);
                $user->save();

                return $this->signAgreement($request);
            default:
                abort(500);
        }
    }

    public function complete(Request $request)
    {
        if (! $request->hasValidSignatureWhileIgnoring(['event'])) {
            abort(500);
        }

        $envelope = DocuSignEnvelope::where('signed_by', $request->user()->id)
            ->where('envelope_id', $request->envelope_id)
            ->withTrashed()
            ->sole();

        switch ($request->event) {
            case 'decline':
                if (! $envelope->complete) {
                    $envelope->delete();
                } else {
                    abort(500);
                }

                break;
            case 'signing_complete':
                $envelope->complete = true;
                $envelope->save();

                if ($envelope->signable_type === Signature::getMorphClassStatic()) {
                    alert()->success('Success!', 'We processed your membership agreement!');
                }

                break;
        }

        return redirect('/');
    }
}
