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
        // TODO: Replace with actual view when created.
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
            return response()->json([
                'error' => true,
                'title' => 'Authentication Error',
                'message' => 'Could not validate email or sponsor is no longer active. Please contact hello@robojackets.org if the issue persists.',
            ], 422);
        }

        // Check if user exists in sponsor_users table; if not, return json error
        if (! $this->sponsorUserExists($email)) {
            // TODO: Redirect to sponsor sign-up page instead of returning error
            return response()->json([
                'error' => true,
                'title' => 'Account Not Found',
                'message' => 'No account found for this email. Please sign up first.',
            ], 404);
        }

        // Get sponsor user (already validated to exist)
        $sponsorUser = SponsorUser::where('email', $email)->first();

        // Generate and dispatch OTP using Spatie
        $sponsorUser->sendOneTimePassword();

        // Cache minimal state for OTP verification.
        session([
            'sponsor_user_id' => $sponsorUser->id,
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
        $request->validate([
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        // Validate session and retrieve user
        $sponsorUserId = session('sponsor_user_id');
        $email = session('sponsor_email');

        if (! $sponsorUserId || ! $email) {
            return $this->errorResponse('Session Expired', 'Your session has expired. Please start the login process again.');
        }

        $sponsorUser = SponsorUser::find($sponsorUserId);
        if (! $sponsorUser) {
            return $this->errorResponse('Authentication Error', 'Unable to find user information. Please start the login process again.');
        }

        // Verify OTP using Spatie
        $result = $sponsorUser->attemptLoginUsingOneTimePassword($request->input('otp'));
        if (! $result->isOk()) {
            return $this->errorResponse('Invalid OTP', $result->validationMessage());
        }

        // Verify sponsor is still active
        $sponsor = $sponsorUser->company;
        if (! $sponsor || ! $sponsor->active()) {
            return $this->errorResponse(
                'Authentication Error',
                'Sponsor information unavailable or no longer active. Please contact hello@robojackets.org.'
            );
        }

        // Establish authenticated session
        $request->session()->regenerate();
        session([
            'sponsor_authenticated' => true,
            'sponsor_id' => $sponsor->id,
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $email,
        ]);
        session()->forget(['sponsor_user_id', 'sponsor_email']);

        return response()->json([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard...',
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
     * Check if user exists in sponsor_users table.
     *
     * @param string $email
     * @return bool
     */
    private function sponsorUserExists(string $email): bool
    {
        return SponsorUser::where('email', $email)->exists();
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
