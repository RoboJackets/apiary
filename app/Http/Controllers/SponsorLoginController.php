<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SponsorDomain;
use App\Models\SponsorUser;
use Illuminate\Http\Request;

// TODO: might need to add server-side rate limiting for OTP

class SponsorLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('sponsor.login');
    }

    public function validateEmail(Request $request)
    {
        // validates input request using Laravel's in-built validator
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,strict,dns,spoof',
                'max:255',
            ],
        ]);

        // reads value
        $email = $request->input('email');

        // checks if domain is valid and sponsor is active; if not, return json error
        if (! $this->isValidSponsorDomain($email)) {
            return $this->errorResponse(
                'Authentication Error',
                'Could not validate email or sponsor is no longer active. '
                .'Contact hello@robojackets.org if the issue persists.'
            );
        }

        // Get sponsor user and check if exists in one query
        $sponsorUser = SponsorUser::where('email', $email)->first();
        if (! $sponsorUser) {
            // Create new unsaved SponsorUser model for new users
            $sponsorUser = new SponsorUser();
            $sponsorUser->email = $email;
        }

        // Generate and dispatch OTP using Spatie
        $sponsorUser->sendOneTimePassword();

        // Cache minimal state for OTP verification.
        session([
            'sponsor_email_pending' => $email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'One-Time Password Sent! Please type the one-time password sent to your email.',
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        // Laravel will automatically throw an error if OTP is invalid
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        // Validate session and retrieve user
        $email = session('sponsor_email_pending');
        if (! $email) {
            return $this->errorResponse(
                'Session Expired',
                'Your session has expired. Please start the login process again.'
            );
        }

        // Retrieve existing user or create temporary one for OTP verification
        $sponsorUser = SponsorUser::where('email', $email)->first();
        if (! $sponsorUser) {
            // Create temporary unsaved user for OTP verification
            $sponsorUser = new SponsorUser();
            $sponsorUser->email = $email;
        }

        // Verify sponsor domain is still valid and active BEFORE verifying OTP
        if (! $this->isValidSponsorDomain($email)) {
            return $this->errorResponse(
                'Authentication Error',
                'Could not validate email or sponsor is no longer active. '
                .'Please contact hello@robojackets.org if the issue persists.'
            );
        }

        // Verify OTP using Spatie
        $result = $sponsorUser->attemptLoginUsingOneTimePassword($request->input('otp'));
        if (! $result->isOk()) {
            return $this->errorResponse('Invalid OTP', $result->validationMessage());
        }

        // Save new user to database after successful OTP verification and sponsor check
        if (! $sponsorUser->exists) {
            $sponsorUser->save();
        }

        // Retrieve sponsor for session data
        $sponsor = $sponsorUser->company;

        // Establish authenticated session
        $request->session()->regenerate();
        session([
            'sponsor_authenticated' => true,
            'sponsor_id' => $sponsor->id,
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $email,
        ]);
        session()->forget('sponsor_email_pending');

        return response()->json([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard...',

            'redirect' => route('home'),
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

    private function errorResponse(string $title, string $message, int $status = 422): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => true,
            'title' => $title,
            'message' => $message,
        ], $status);
    }
}
