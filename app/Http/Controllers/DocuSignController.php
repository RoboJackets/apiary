<?php

declare(strict_types=1);

// phpcs:disable Generic.Commenting.DocComment.MissingShort
// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion

namespace App\Http\Controllers;

use App\Jobs\SendDocuSignEnvelopeForTravelAssignment;
use App\Models\DocuSignEnvelope;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use App\Models\TravelAssignment;
use App\Notifications\Nova\LinkDocuSignAccount;
use App\Util\DocuSign;
use Carbon\CarbonImmutable;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Client\Auth\OAuthToken;
use DocuSign\eSign\Client\Auth\UserInfo;
use DocuSign\eSign\Model\RecipientViewRequest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
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
                ->whereHas(
                    'travel',
                    static function (Builder $query): void {
                        $query->whereIn('status', ['approved', 'complete']);
                    }
                )
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
            return self::redirectToOAuthConsent(request: $request, next: 'signTravel', impersonation: false);

            // When the user returns to Apiary, their DocuSign credentials will be stored in handleProviderCallback,
            // then this method (signTravel) will be called recursively to continue the signing flow
        }

        SendDocuSignEnvelopeForTravelAssignment::dispatchSync($assignment);

        try {
            return self::redirectToRecipientView(
                $request,
                $recipientApiClient,
                $assignment->envelope()->whereNotNull('envelope_id')->sole()
            );
        } catch (ModelNotFoundException) {
            if (
                ! $assignment->user->has_emergency_contact_information &&
                $assignment->travel->needs_travel_information_form
            ) {
                return view(
                    'travel.actionrequired',
                    [
                        'name' => $assignment->travel->name,
                        'action' => 'provide emergency contact information on your profile',
                    ]
                );
            }

            if ($assignment->user->phone === null && $assignment->travel->needs_airfare_form) {
                return view(
                    'travel.actionrequired',
                    [
                        'name' => $assignment->travel->name,
                        'action' => 'provide your phone number on your profile',
                    ]
                );
            }

            return response(status: 500);
        }
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
            return self::redirectToOAuthConsent(request: $request, next: 'signAgreement', impersonation: false);

            // When the user returns to Apiary, their DocuSign credentials will be stored in handleProviderCallback,
            // then this method (signAgreement) will be called recursively to continue the signing flow
        }

        return Cache::lock(name: $user->uid.'_docusign', seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($template, $user, $recipientApiClient, $request) {
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

                return self::redirectToRecipientView($request, $recipientApiClient, $envelope);
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
        return self::redirectToOAuthConsent(request: $request, next: 'getGlobalToken', impersonation: true);
    }

    public function redirectUserToProvider(Request $request)
    {
        return self::redirectToOAuthConsent(request: $request, next: 'getUserToken', impersonation: false);
    }

    public function redirectUserToProviderDeepLink(Request $request, string $resource, string $resourceId)
    {
        $request->session()->put('deepLinkResource', $resource);
        $request->session()->put('deepLinkResourceId', $resourceId);

        ray($request->session()->all());

        return self::redirectToOAuthConsent(request: $request, next: 'getUserTokenDeepLink', impersonation: false);
    }

    /**
     * Handle the OAuth consent response from DocuSign.
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

            Log::error('DocuSign user is not in the configured account', $userinfo_serialized);

            abort(401);
        }

        $request
            ->user()
            ->novaNotifications()
            ->where('type', LinkDocuSignAccount::class)
            ->delete();

        switch ($request->session()->get('next')) {
            case 'getGlobalToken':
                // In this case, we just need to output the user ID so that it can be stored in the environment.
                // This doesn't do anything interesting in DocuSign, it's just confirmation that the app has access to
                // impersonate a user at this point.

                $userinfo_serialized = DocuSign::serializeUserInfo($userinfo);

                Log::info('Successfully authenticated with DocuSign', $userinfo_serialized);

                DocuSign::getApiClient();

                return response()->json($userinfo_serialized);
            case 'getUserToken':
                // In this case, a user has just authenticated with DocuSign from the Nova menu option.
                // We need to store the credentials in their user model, then send them back to Nova.

                self::storeUserDocuSignCredentials($request, $userinfo, $tokens);

                return redirect(route('nova.pages.dashboard.custom', ['name' => 'main']));
            case 'getUserTokenDeepLink':
                // In this case, a user has just authenticated with DocuSign from a trip detail page.
                // We need to store the credentials in their user model, then send them back to their trip detail page.

                self::storeUserDocuSignCredentials($request, $userinfo, $tokens);

                ray($request->session()->all());

                return redirect(
                    route(
                        'nova.pages.detail',
                        [
                            'resource' => $request->session()->get('deepLinkResource'),
                            'resourceId' => $request->session()->get('deepLinkResourceId'),
                        ]
                    )
                );
            case 'signAgreement':
                // In this case, a user has just authenticated with DocuSign after starting the signing flow.
                // We need to store the credentials in their user model, then continue with signing.

                self::storeUserDocuSignCredentials($request, $userinfo, $tokens);

                return $this->signAgreement($request);
            case 'signTravel':
                // In this case, a user has just authenticated with DocuSign after starting the signing flow.
                // We need to store the credentials in their user model, then continue with signing.

                self::storeUserDocuSignCredentials($request, $userinfo, $tokens);

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

    private static function storeUserDocuSignCredentials(Request $request, UserInfo $userInfo, OAuthToken $tokens): void
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($userInfo->getEmail() !== $user->uid.'@gatech.edu') {
            $userinfo_serialized = DocuSign::serializeUserInfo($userInfo);

            Log::error('User email from DocuSign does not match authenticated Apiary user', $userinfo_serialized);

            abort(401);
        }

        $user->docusign_access_token = $tokens->getAccessToken();
        $user->docusign_access_token_expires_at = CarbonImmutable::now()->addSeconds(
            intval($tokens->getExpiresIn()) - 60
        );
        $user->docusign_refresh_token = $tokens->getRefreshToken();
        $user->docusign_refresh_token_expires_at = CarbonImmutable::now()->addDays(29);
        $user->save();
    }

    private static function redirectToOAuthConsent(
        Request $request,
        string $next,
        bool $impersonation
    ): RedirectResponse {
        $state = DocuSign::getState();

        $request->session()->put('state', $state);
        $request->session()->put('next', $next);

        return redirect(
            DocuSign::getApiClient(withAccessToken: false)->getAuthorizationURI(
                client_id: config('docusign.client_id'),
                scopes: $impersonation ? [
                    ApiClient::$SCOPE_SIGNATURE,
                    ApiClient::$SCOPE_IMPERSONATION,
                ] : [
                    ApiClient::$SCOPE_SIGNATURE,
                ],
                redirect_uri: route('docusign.auth.complete'),
                response_type: 'code',
                state: $state
            ).($impersonation ? '&prompt=login' : '&login_hint='.$request->user()->uid.'@gatech.edu')
        );
    }

    private static function redirectToRecipientView(
        Request $request,
        ApiClient $recipientApiClient,
        DocuSignEnvelope $envelope
    ): RedirectResponse {
        return redirect((new EnvelopesApi($recipientApiClient))->createRecipientView(
            account_id: config('docusign.account_id'),
            envelope_id: $envelope->envelope_id,
            recipient_view_request: (new RecipientViewRequest())
                ->setAuthenticationInstant($request->session()->get('authenticationInstant'))
                ->setAuthenticationMethod('SingleSignOn_Other')
                ->setEmail($request->user()->uid.'@gatech.edu')
                ->setUserName($request->user()->full_name)
                ->setReturnUrl(
                    URL::signedRoute('docusign.complete', ['envelope_id' => $envelope->envelope_id])
                )
                ->setSecurityDomain(config('cas.cas_hostname'))
                ->setXFrameOptions('deny')
        )->getUrl());
    }
}
