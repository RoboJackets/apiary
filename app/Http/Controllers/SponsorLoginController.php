<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\SponsorOtp;
use App\Models\SponsorDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class SponsorLoginController extends Controller
{
    /**
     * Display the sponsor login page.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // TODO: Replace 'sponsor.login' with actual view path when created (not created yet)
        // The view should contain:
        // - Email input field
        // - "Next" button
        // - Form that POSTs to the validateEmail route
        return view('sponsor.login');
    }

    /**
     * Validate sponsor email and proceed to OTP step.
     *
     * This method validates the email from the sponsor login form:
     * - Ensures it's a valid email format
     * - Checks for spoofing attempts
     * - Verifies the domain exists in sponsor_domains table
     * - Confirms the associated sponsor is still active
     */
    public function validateEmail(Request $request)
    {
        $email = $request->input('email');
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Rate limiting: Max 5 OTP requests per email per 15 minutes
        $emailKey = 'sponsor-otp-email:' . $email;
        if (RateLimiter::tooManyAttempts($emailKey, 5)) {
            $seconds = RateLimiter::availableIn($emailKey);
            $minutes = ceil($seconds / 60);
            
            return response()->json([
                'error' => true,
                'title' => 'Too Many Requests',
                'message' => "Too many OTP requests. Please try again in {$minutes} minute(s).",
            ], 429);
        }

        // Rate limiting: Max 10 OTP requests per IP per hour
        $ipKey = 'sponsor-otp-ip:' . $ipAddress;
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $minutes = ceil($seconds / 60);
            
            return response()->json([
                'error' => true,
                'title' => 'Too Many Requests',
                'message' => "Too many requests from your location. Please try again in {$minutes} minute(s).",
            ], 429);
        }

        // Basic validation for required and email format
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,strict,dns,spoof', // RFC compliance, strict mode, DNS check, spoof detection
                'max:255',
            ],
        ]);

        // Check if email domain is in sponsor_domains table
        if (! $this->isValidSponsorDomain($email)) {
            // Increment rate limit attempts even on validation failure to prevent abuse
            RateLimiter::hit($emailKey, 900); // 15 minutes
            RateLimiter::hit($ipKey, 3600); // 1 hour
            
            // TODO: Replace with actual SweetAlert response
            // For now, return JSON for frontend to handle
            return response()->json([
                'error' => true,
                'title' => 'Authentication Error',
                'message' => 'Could not validate email. Please try again or contact hello@robojackets.org if the issue persists.',
            ], 422);
        }

        // Check if the sponsor is still active
        if (!$this->isSponsorActive($email)) {
            // Increment rate limit attempts
            RateLimiter::hit($emailKey, 900); // 15 minutes
            RateLimiter::hit($ipKey, 3600); // 1 hour
            
            // TODO: Replace with actual SweetAlert response
            return response()->json([
                'error' => true,
                'title' => 'Authentication Error',
                'message' => 'The sponsor associated with this email is no longer active. For more information or to reinstate access, please contact hello@robojackets.org.',
            ], 422);
        }

        // Email is valid - proceed to OTP generation and sending
        
        // Generate OTP using Spatie's facade
        // This creates a 6-digit OTP and stores it in the database with 10-minute expiration
        $otp = \Spatie\OneTimePasswords\Support\PasswordGenerators\NumericOneTimePasswordGenerator::class;
        $generator = new $otp();
        $otpCode = $generator->generate();
        
        // Hash the OTP for secure storage
        $hashedOtp = Hash::make($otpCode);
        
        // Store OTP data in session for verification
        session([
            'sponsor_email' => $email,
            'sponsor_otp_hash' => $hashedOtp,
            'sponsor_otp_expires' => now()->addMinutes(10),
            'sponsor_otp_attempts' => 0,
        ]);
        
        // Send OTP via email using Mailgun
        Mail::send(new SponsorOtp($otpCode, $email));
        
        // Increment rate limit counters on successful send
        RateLimiter::hit($emailKey, 900); // 15 minutes
        RateLimiter::hit($ipKey, 3600); // 1 hour
        
    // Successful OTP request (logging removed)

        // Return success response
        // TODO: Frontend will display OTP input box below the email form (no separate page)
        // The UI should show the blue notification box with:
        // - "One-Time Password Sent!" message
        // - Instructions to check spam folder
        // - OTP input field
        // - Submit button
        return response()->json([
            'success' => true,
            'message' => 'One-Time Password Sent! Please type the one-time password sent to your email. ',
        ], 200);
    }

    /**
     * Verify the OTP submitted by the sponsor.
     *
     * This method validates the submitted OTP:
     * - Checks if OTP exists and hasn't expired
     * - Verifies the OTP matches the hashed value
     * - Tracks failed attempts and locks after 3 failures
     * - Logs in the sponsor if successful
     */
    public function verifyOtp(Request $request)
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        
        // Rate limiting: Max 10 verification attempts per IP per 5 minutes
        $verifyIpKey = 'sponsor-verify-ip:' . $ipAddress;
        if (RateLimiter::tooManyAttempts($verifyIpKey, 10)) {
            $seconds = RateLimiter::availableIn($verifyIpKey);
            $minutes = ceil($seconds / 60);
            // Rate limit exceeded (logging removed)
            
            return response()->json([
                'error' => true,
                'title' => 'Too Many Attempts',
                'message' => "Too many verification attempts. Please try again in {$minutes} minute(s).",
            ], 429);
        }
        
        // Validate OTP input
        $request->validate([
            'otp' => [
                'required',
                'string',
                'digits:6', // Must be exactly 6 digits
            ],
        ]);

        $otp = $request->input('otp');
        $email = session('sponsor_email');
        $hashedOtp = session('sponsor_otp_hash');
        $expiresAt = session('sponsor_otp_expires');
        $attempts = session('sponsor_otp_attempts', 0);

        // Check if email is in session
        if (!$email || !$hashedOtp) {
            RateLimiter::hit($verifyIpKey, 300); // 5 minutes

            return response()->json([
                'error' => true,
                'title' => 'Session Expired',
                'message' => 'Your session has expired. Please start the login process again.',
            ], 422);
        }

        // Check if OTP has expired
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            session()->forget(['sponsor_email', 'sponsor_otp_hash', 'sponsor_otp_expires', 'sponsor_otp_attempts']);
            RateLimiter::hit($verifyIpKey, 300); // 5 minutes

            return response()->json([
                'error' => true,
                'title' => 'OTP Expired',
                'message' => 'Your one-time password has expired. Please request a new one.',
            ], 422);
        }

        // Check if OTP is locked due to too many attempts
        if ($attempts >= 3) {
            RateLimiter::hit($verifyIpKey, 300); // 5 minutes

            return response()->json([
                'error' => true,
                'title' => 'Too Many Attempts',
                'message' => 'This OTP has been locked due to too many failed attempts. Please request a new one.',
            ], 422);
        }

        // Verify the OTP against stored hash
        if (!Hash::check($otp, $hashedOtp)) {
            // Increment failed attempt counter
            session(['sponsor_otp_attempts' => $attempts + 1]);
            RateLimiter::hit($verifyIpKey, 300); // 5 minutes

            $attemptsRemaining = 3 - ($attempts + 1);

            return response()->json([
                'error' => true,
                'title' => 'Invalid OTP',
                'message' => "The one-time password you entered is incorrect. You have {$attemptsRemaining} attempt(s) remaining.",
            ], 422);
        }

        // OTP is correct - proceed with login
        // Delete OTP from session (one-time use)
        session()->forget(['sponsor_otp_hash', 'sponsor_otp_expires', 'sponsor_otp_attempts']);

        // Get the sponsor associated with this email
        $domain = substr(strrchr($email, '@'), 1);
        $sponsorDomain = SponsorDomain::where('domain_name', $domain)->first();

        if (!$sponsorDomain || !$sponsorDomain->sponsor) {
            RateLimiter::hit($verifyIpKey, 300); // 5 minutes

            return response()->json([
                'error' => true,
                'title' => 'Authentication Error',
                'message' => 'Unable to find sponsor information. Please contact hello@robojackets.org.',
            ], 422);
        }

        $sponsor = $sponsorDomain->sponsor;

        // Double-check sponsor is still active
        if (!$sponsor->active()) {
            RateLimiter::hit($verifyIpKey, 300); // 5 minutes

            return response()->json([
                'error' => true,
                'title' => 'Sponsor Inactive',
                'message' => 'This sponsor account is no longer active. Please contact hello@robojackets.org.',
            ], 422);
        }

        // TODO: Implement sponsor authentication/session
        // This requires setting up a sponsor auth guard in config/auth.php
        // For now, store sponsor info in session
        session([
            'sponsor_authenticated' => true,
            'sponsor_id' => $sponsor->id,
            'sponsor_name' => $sponsor->name,
            'sponsor_email' => $email,
        ]);

        // Clear the email from session
        session()->forget('sponsor_email');
        
        // Clear rate limiter on successful login
        RateLimiter::clear($verifyIpKey);
        
    // Successful login (logging removed)

        // Return success response
        // TODO: Frontend should redirect to sponsor dashboard
        return response()->json([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard...',
            'redirect' => route('sponsor.dashboard'), // TODO: Create this route
        ], 200);
    }

    /**
     * Check if the email domain is in the sponsor_domains table.
     *
     * @param string $email
     * @return bool
     */
    private function isValidSponsorDomain(string $email): bool
    {
        // Use the existing SponsorDomain method to check if email is associated with a sponsor
        return SponsorDomain::sponsorEmail($email);
    }

    /**
     * Check if the sponsor associated with the email domain is still active.
     *
     * @param string $email
     * @return bool
     */
    private function isSponsorActive(string $email): bool
    {
        // Extract domain from email
        $domain = substr(strrchr($email, '@'), 1);
        
        // Find the sponsor domain
        $sponsorDomain = SponsorDomain::where('domain_name', $domain)->first();
        
        // If no sponsor domain found, return false
        if (!$sponsorDomain) {
            return false;
        }
        
        // Check if the sponsor is active (end_date is in the future)
        return $sponsorDomain->sponsor && $sponsorDomain->sponsor->active();
    }

    // Logging functionality removed per request. All audit/log calls were stripped from this controller.
}
