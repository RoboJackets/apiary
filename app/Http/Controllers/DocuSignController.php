<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion

namespace App\Http\Controllers;

use App\Jobs\SendDocuSignEnvelopeForTravelAssignment;
use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\TravelAssignment;
use App\Util\DocuSign;
use Carbon\Carbon;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Model\RecipientViewRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            ).'&login_hint='.$user->uid.'@gatech.edu';

            $request->session()->put('state', $state);
            $request->session()->put('next', 'signTravel');

            return redirect($authorization_uri);

            // When the user returns to Apiary, their DocuSign credentials will be stored in handleProviderCallback,
            // then this method (signTravel) will be called recursively to continue the signing flow
        }

        SendDocuSignEnvelopeForTravelAssignment::dispatchSync($assignment);

        $authenticationInstant = $request->session()->get('authenticationInstant');

        $envelope = $assignment->envelope()->whereNotNull('envelope_id')->sole();

        $recipientViewResponse = (new EnvelopesApi($recipientApiClient))->createRecipientView(
            account_id: config('docusign.account_id'),
            envelope_id: $envelope->envelope_id,
            recipient_view_request: (new RecipientViewRequest())
                ->setAuthenticationInstant($authenticationInstant)
                ->setAuthenticationMethod('SingleSignOn_Other')
                ->setEmail($user->uid.'@gatech.edu')
                ->setUserName($user->full_name)
                ->setReturnUrl(
                    URL::signedRoute('docusign.complete', ['envelope_id' => $envelope->envelope_id])
                )
                ->setSecurityDomain(config('cas.cas_hostname'))
                ->setXFrameOptions('deny')
        );

        return redirect($recipientViewResponse->getUrl());
    }

    /**
     * Redirect to a DocuSign signing session for the membership agreement.
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
            ).'&login_hint='.$user->uid.'@gatech.edu';

            $request->session()->put('state', $state);
            $request->session()->put('next', 'signAgreement');

            return redirect($authorization_uri);

            // When the user returns to Apiary, their DocuSign credentials will be stored in handleProviderCallback,
            // then this method (signAgreement) will be called recursively to continue the signing flow
        }

        $authenticationInstant = $request->session()->get('authenticationInstant');

        return Cache::lock(name: $user->uid.'_docusign', seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($template, $user, $recipientApiClient, $authenticationInstant) {
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
                        envelope_definition: DocuSign::membershipAgreementEnvelopeDefinition($envelope)
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
                        ->setAuthenticationInstant($authenticationInstant)
                        ->setAuthenticationMethod('SingleSignOn_Other')
                        ->setEmail($user->uid.'@gatech.edu')
                        ->setUserName($user->full_name)
                        ->setReturnUrl(
                            URL::signedRoute('docusign.complete', ['envelope_id' => $envelope->envelope_id])
                        )
                        ->setSecurityDomain(config('cas.cas_hostname'))
                        ->setXFrameOptions('deny')
                );

                return redirect($recipientViewResponse->getUrl());
            }
        );
    }

    /**
     * This route is ONLY used as a convenience to set up the globally-shared DocuSign credentials.
     *
     * Redirects for standard users are handled as part of signAgreement.
     */
    public function redirectGlobalToProvider(Request $request)
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

    public function redirectUserToProvider(Request $request)
    {
        $state = DocuSign::getState();

        $docusign = DocuSign::getApiClient(withAccessToken: false);

        $authorization_uri = $docusign->getAuthorizationURI(
            client_id: config('docusign.client_id'),
            scopes: [ApiClient::$SCOPE_SIGNATURE],
            redirect_uri: route('docusign.auth.complete'),
            response_type: 'code',
            state: $state
        ).'&login_hint='.$request->user()->uid.'@gatech.edu';

        $request->session()->put('state', $state);
        $request->session()->put('next', 'getUserToken');

        return redirect($authorization_uri);
    }

    /**
     * Handle the OAuth consent response from DocuSign.
     *
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
            throw new Exception('Missing required request parameters or state does not match');
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
            case 'getUserToken':
                // In this case, a user has just authenticated with DocuSign from the Nova menu option.
                // We need to store the credentials in their user model, then send them back to Nova.

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

                return redirect(route('nova.pages.dashboard.custom', ['name' => 'main']));
            case 'getGlobalToken':
                // In this case, we just need to output the user ID so that it can be stored in the environment.
                // This doesn't do anything interesting in DocuSign, it's just confirmation that the app has access to
                // impersonate a user at this point.

                $userinfo_serialized = DocuSign::serializeUserInfo($userinfo);

                Log::info($userinfo_serialized);

                DocuSign::getApiClient();

                return response()->json($userinfo_serialized);
            case 'signAgreement':
                // In this case, a user has just authenticated with DocuSign after starting the signing flow.
                // We need to store the credentials in their user model, then continue with signing.

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
            case 'signTravel':
                // In this case, a user has just authenticated with DocuSign after starting the signing flow.
                // We need to store the credentials in their user model, then continue with signing.

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

                return $this->signTravel($request);
            default:
                throw new Exception('Unexpected next action "'.$request->session()->get('next').'"');
        }
    }

    public function complete(Request $request)
    {
        if (! $request->hasValidSignatureWhileIgnoring(['event'])) {
            throw new Exception('Invalid signature');
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
                    throw new Exception('Attempted to decline a completed envelope');
                }

                break;
            case 'signing_complete':
                if ($envelope->deleted_at !== null) {
                    throw new Exception('Attempted to complete a deleted envelope');
                }

                if ($envelope->signable_type === Signature::getMorphClassStatic()) {
                    if ($request->user()->needs_parent_or_guardian_signature) {
                        alert()->success(
                            'Success!',
                            'Your membership agreement has been sent to your parent or guardian for signature.'
                        );
                    } else {
                        $envelope->complete = true;
                        $envelope->save();

                        alert()->success('Success!', 'We processed your membership agreement.');
                    }
                } elseif ($envelope->signable_type === TravelAssignment::getMorphClassStatic()) {
                    $envelope->complete = true;
                    $envelope->save();

                    alert()->success('Success!', 'We processed your travel forms.');
                }

                break;
        }

        return redirect('/');
    }
}
