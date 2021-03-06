<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\MembershipAgreementCompleteRequest;
use App\Http\Requests\MembershipAgreementRedirectRequest;
use App\Jobs\RetrieveIpAddressGeoLocationForSignature;
use App\Models\MembershipAgreementTemplate;
use App\Models\Signature;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    /**
     * Generate a print version of the agreement for people who need to be difficult.
     */
    public function print(Request $request)
    {
        $user = $request->user();
        $template = MembershipAgreementTemplate::orderByDesc('updated_at')->firstOrFail();

        if ($user->hasSignedLatestAgreement()) {
            return view('agreement.alreadysigned');
        }

        $signature = Signature::firstOrNew(
            [
                'membership_agreement_template_id' => $template->id,
                'user_id' => $user->id,
                'electronic' => false,
                'complete' => false,
            ]
        );

        $signature->render_timestamp = Carbon::now();
        $signature->save();

        return PDF::loadView(
            'agreement.print',
            [
                'text' => $template->renderForUser($user, $signature->electronic),
                'template' => $template,
            ]
        )->stream('agreement.pdf');
    }

    /**
     * Render the agreement in the browser.
     */
    public function render(Request $request)
    {
        $user = $request->user();
        $template = MembershipAgreementTemplate::orderByDesc('updated_at')->firstOrFail();

        if ($user->hasSignedLatestAgreement()) {
            return view('agreement.alreadysigned');
        }

        $signature = Signature::firstOrNew(
            [
                'membership_agreement_template_id' => $template->id,
                'user_id' => $user->id,
                'electronic' => true,
                'complete' => false,
            ]
        );

        $ip = $request->ip();

        if (null === $ip) { // I have no idea what could possibly cause this, but that's what the contract says
            return view(
                'agreement.error',
                [
                    'message' => 'We could not detect your IP address.',
                ]
            );
        }

        $signature->ip_address = $ip;
        $signature->user_agent = $request->header('User-Agent');
        $signature->render_timestamp = Carbon::now();
        $signature->save();

        return view(
            'agreement.render',
            [
                'text' => $template->renderForUser($user, $signature->electronic),
            ]
        );
    }

    /**
     * Redirect the user to CAS as the penultimate signing step.
     */
    public function redirect(MembershipAgreementRedirectRequest $request)
    {
        $user = $request->user();
        $template = MembershipAgreementTemplate::orderByDesc('updated_at')->firstOrFail();

        if ($user->hasSignedLatestAgreement()) {
            return view('agreement.alreadysigned');
        }

        $signature = Signature::where('membership_agreement_template_id', $template->id)
            ->where('user_id', $user->id)
            ->where('electronic', true)
            ->where('complete', false)
            ->firstOrFail();

        $deviceCheck = $this->ipAddressAndUserAgentMatch($signature, $request);

        if (null !== $deviceCheck) {
            return $deviceCheck;
        }

        $signature->redirect_to_cas_timestamp = Carbon::now();

        $signature->cas_host = config('cas.cas_hostname');
        $signature->cas_service_url_hash = Signature::hash(
            $signature->user->uid,
            $signature->ip_address,
            $signature->user_agent,
            $signature->redirect_to_cas_timestamp
        );
        $signature->save();

        RetrieveIpAddressGeoLocationForSignature::dispatch($signature);

        return redirect(
            'https://'.$signature->cas_host.'/cas/login?renew=true&service='.urlencode(
                route(
                    'agreement.complete',
                    [
                        'hash' => $signature->cas_service_url_hash,
                    ]
                )
            )
        );
    }

    /**
     * Verify and store CAS data then complete the signature.
     */
    public function complete(MembershipAgreementCompleteRequest $request)
    {
        $signature = Signature::where('cas_service_url_hash', $request->input('hash'))->firstOrFail();

        $deviceCheck = $this->ipAddressAndUserAgentMatch($signature, $request);

        if (null !== $deviceCheck) {
            return $deviceCheck;
        }

        $client = new Client(
            [
                'base_uri' => 'https://'.$signature->cas_host,
                'headers' => [
                    'User-Agent' => 'Apiary on '.config('app.url'),
                ],
                'http_errors' => true,
                'allow_redirects' => false,
            ]
        );

        $response = $client->get(
            '/cas/serviceValidate',
            [
                'query' => [
                    'service' => route(
                        'agreement.complete',
                        [
                            'hash' => $signature->cas_service_url_hash,
                        ]
                    ),
                    'ticket' => $request->input('ticket'),
                    'renew' => 'true',
                ],
            ]
        );

        $responseContents = $response->getBody()->getContents();

        if (200 !== $response->getStatusCode()) {
            Log::error(self::class.' CAS said '.$responseContents);

            return view(
                'agreement.error',
                [
                    'message' => 'Georgia Tech Login returned an error while validating your identity.',
                ]
            );
        }

        $username = $this->getUsernameFromCasResponse($responseContents);

        if (null === $username) {
            Log::error(self::class.' CAS said '.$responseContents);

            return view(
                'agreement.error',
                [
                    'message' => 'Georgia Tech Login returned an error while validating your identity.',
                ]
            );
        }

        if (strtolower($username) !== strtolower($signature->user->uid)) {
            return view(
                'agreement.error',
                [
                    'message' => 'Usernames do not match.',
                ]
            );
        }

        $signature->cas_ticket = $request->input('ticket');
        $signature->cas_ticket_redeemed_timestamp = Carbon::now();
        $signature->complete = true;
        $signature->save();

        alert()->success('Agreement saved!', 'Success!');

        return redirect('/');
    }

    private function getUsernameFromCasResponse(string $text_response): ?string
    {
        // This function is borrowed from the phpCAS library with all the error handling replaced

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->encoding = 'utf-8';

        if (false === $dom->loadXML($text_response)) {
            return null;
        }

        $tree_response = $dom->documentElement;

        if (null === $tree_response) {
            return null;
        }

        if ('serviceResponse' !== $tree_response->localName) {
            return null;
        }

        if (0 !== $tree_response->getElementsByTagName('authenticationFailure')->length) {
            return null;
        }

        if (0 === $tree_response->getElementsByTagName('authenticationSuccess')->length) {
            return null;
        }

        $success_elements = $tree_response->getElementsByTagName('authenticationSuccess');

        $authenticationSuccessElement = $success_elements->item(0);

        if (null === $authenticationSuccessElement) {
            return null;
        }

        $userElements = $authenticationSuccessElement->getElementsByTagName('user');

        if (0 === $userElements->length) {
            return null;
        }

        $element = $userElements->item(0);

        if (null === $element) {
            return null;
        }

        return trim($element->nodeValue);
    }

    private function ipAddressAndUserAgentMatch(Signature $signature, Request $request)
    {
        if ($signature->ip_address !== $request->ip()) {
            return view(
                'agreement.error',
                [
                    'message' => 'Your IP address has changed since beginning the signature process.',
                ]
            );
        }

        if ($signature->user_agent !== $request->header('User-Agent')) {
            return view(
                'agreement.error',
                [
                    'message' => 'Your User-Agent header has changed since beginning the signature process.',
                ]
            );
        }

        return null;
    }
}
