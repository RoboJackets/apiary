<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SponsorDomain;
use App\Models\SponsorUser;
use Illuminate\Http\Request;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;

class SponsorLoginController extends Controller
{
    /**
     * Show sponsor login page.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // TODO: Replace with actual view when created
        return view('sponsor.login');
    }

    /**
     * Validate email and send OTP.
     *
     * Validates format, checks spoofing, verifies domain, and ensures sponsor is active.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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
                'Could not validate email or sponsor is no longer active. Contact hello@robojackets.org if the issue persists.'
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
            'sponsor_email' => $email,
        ]);

        // TODO: Frontend should show message somehow
        return response()->json([
            'success' => true,
            'message' => 'One-Time Password Sent! Please type the one-time password sent to your email.',
        ], 200);
    }

    /**
     * Verify OTP and establish session.
     *
     * Validates input, checks session context, verifies OTP, and stores sponsor session data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        // Laravel will automatically throw an error if OTP is invalid
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);
        // TODO: Override error message?

        // Validate session and retrieve user
        $email = session('sponsor_email');
        if (! $email) {
            return $this->errorResponse('Session Expired', 'Your session has expired. Please start the login process again.');
        }

        // Retrieve existing user or create temporary one for OTP verification
        $sponsorUser = SponsorUser::where('email', $email)->first();
        if (! $sponsorUser) {
            // Create temporary unsaved user for OTP verification
            $sponsorUser = new SponsorUser();
            $sponsorUser->email = $email;
        }

        // Verify OTP using Spatie
        $result = $sponsorUser->attemptLoginUsingOneTimePassword($request->input('otp'));
        if (! $result->isOk()) {
            return $this->errorResponse('Invalid OTP', $result->validationMessage());
        }

        // Verify sponsor domain is still valid and active before saving new user
        if (! $this->isValidSponsorDomain($email)) {
            return $this->errorResponse(
                'Authentication Error',
                'Could not validate email or sponsor is no longer active. Please contact hello@robojackets.org if the issue persists.'
            );
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
        session()->forget(['sponsor_email']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard...',
            // TODO: change to correct route
            'redirect' => route('sponsor.dashboard'), 
        ]);
    }

    /**
     * Check if email domain is valid and sponsor is active.
     *
     * @param string $email
     * @return bool
     */
    private function isValidSponsorDomain(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);
        $sponsorDomain = SponsorDomain::where('domain_name', $domain)->first();

        if (! $sponsorDomain) {
            return false;
        }

        return $sponsorDomain->sponsor && $sponsorDomain->sponsor->active();
    }

    /**
     * Return a standardized error response.
     *
     * @param string $title
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    private function errorResponse(string $title, string $message, int $status = 422): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => true,
            'title' => $title,
            'message' => $message,
        ], $status);
    }
}
