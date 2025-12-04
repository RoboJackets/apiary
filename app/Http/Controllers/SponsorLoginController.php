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

    public function validateEmail(Request $request): JsonResponse
    {
        // Validate input request using Laravel's in-built validator
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,strict,dns,spoof',
                'max:255',
            ],
        ]);

        // Read value - cast to string since validation guarantees it
        $email = (string) $request->input('email');

        // Check if domain is valid and sponsor is active; if not, return JSON error
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

        // Generate and dispatch OTP using Spatie
        $sponsorUser->sendOneTimePassword();

        // Cache minimal state for OTP verification
        session([
            'sponsor_email_pending' => $email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'One-Time Password Sent! Please type the one-time password sent to your email.',
        ], 200);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        // Laravel will automatically throw an error if OTP is invalid
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        // Validate session and retrieve user
        $email = session('sponsor_email_pending');
        if (! is_string($email) || $email === '') {
            return $this->errorResponse(
                'Session Expired',
                'Your session has expired. Please start the login process again.'
            );
        }

        // Retrieve existing user for OTP verification
        // sole() ensures exactly one user exists (throws exception if 0 or >1 found)
        $sponsorUser = SponsorUser::where('email', $email)->sole();

        // Verify sponsor domain is still valid and active BEFORE verifying OTP
        if (! $this->isValidSponsorDomain($email)) {
            return $this->errorResponse(
                'Authentication Error',
                'Could not validate email or sponsor is no longer active. '.
                'Please contact hello@robojackets.org if the issue persists.'
            );
        }

        // Verify OTP using Spatie
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
        // Dummy comment to force a build on Github site.
        return response()->json([
            'error' => true,
            'title' => $title,
            'message' => $message,
        ], $status);
    }
}
