<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Models\SponsorDomain;
use App\Models\SponsorUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SponsorLoginController
{
    public function showLoginForm()
    {
        return view('sponsors.login');
    }

    /**
     * Ensures that an email given for sponsor login belongs to a sponsoring company.
     *
     * @return JsonResponse depending on whether the email is valid and belongs to a sponsor
     */
    public function validateEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,strict,dns,spoof',
                'max:255',
            ],
        ]);

        $email = (string) $request->input('email');

        if (! $this->isValidSponsorDomain($email)) {
            return $this->errorResponse(
                'Authentication Error',
                'Could not validate email or sponsor is no longer active. '.
                'Contact hello@robojackets.org if the issue persists.'
            );
        }

        $sponsorUser = SponsorUser::where('email', $email)->first();
        if (! $sponsorUser) {
            $sponsorUser = new SponsorUser();
            $sponsorUser->email = $email;
            $sponsorUser->sponsor_id = Sponsor::whereHas(
                'domainNames',
                static fn ($q) => $q->where('domain_name', substr(strrchr($email, '@'), 1))
            )
                ->firstOrFail()
                ->id;
            $sponsorUser->save();
        }

        if (! $sponsorUser->should_receive_email) {
            return $this->errorResponse(
                'Email Delivery Error',
                'We are unable to send emails to this address. '.
                'Please contact hello@robojackets.org for assistance.'
            );
        }

        $sponsorUser->sendOneTimePassword();

        session([
            'sponsor_email_pending' => $email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'One-Time Password Sent! Please type the one-time password sent to your email.',
        ], 200);
    }

    /**
     * Verifies that the one-time password is a six-digit code.
     * The code must have been sent within the time limit in config/one-time-passwords.php.
     * Redirects to sponsor home page if the code is correct for the user.
     *
     * @return JsonResponse contents depend on whether or not the code was valid.
     */
    public function verifyOneTimePassword(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $email = session('sponsor_email_pending');
        if (! is_string($email) || $email === '') {
            return $this->errorResponse(
                'Session Expired',
                'Your session has expired. Please start the login process again.'
            );
        }

        $sponsorUser = SponsorUser::where('email', $email)->sole();

        if (! $this->isValidSponsorDomain($email)) {
            return $this->errorResponse(
                'Authentication Error',
                'Could not validate email or sponsor is no longer active. '.
                'Please contact hello@robojackets.org if the issue persists.'
            );
        }

        $otp = (string) $request->input('otp');
        $result = $sponsorUser->attemptLoginUsingOneTimePassword($otp);
        if (! $result->isOk()) {
            return $this->errorResponse('Invalid OTP', $result->validationMessage());
        }

        Auth::guard('sponsor')->login($sponsorUser);

        session()->forget('sponsor_email_pending');

        return response()->json([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard...',
            'redirect' => route('sponsor.home'),
        ]);
    }

    private function isValidSponsorDomain(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);
        $sponsorDomain = SponsorDomain::where('domain_name', $domain)->first();

        if (! $sponsorDomain) {
            return false;
        }

        return $sponsorDomain->sponsor && $sponsorDomain->sponsor->active();
    }

    private function errorResponse(string $title, string $message, int $status = 422): JsonResponse
    {
        return response()->json([
            'error' => true,
            'title' => $title,
            'message' => $message,
        ], $status);
    }
}
