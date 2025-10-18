<?php

namespace App\Rules;

use App\Models\Sponsor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SponsorUserValidEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sponsorInput = request()->input('company');
        $sponsorId = null;

        if (is_array($sponsorInput) && isset($sponsorInput['resourceId'])) {
            $sponsorId = $sponsorInput['resourceId'];
        } elseif (is_scalar($sponsorInput)) {
            $sponsorId = $sponsorInput;
        } elseif ($tmp = request()->input('company_id')) {
            $sponsorId = $tmp;
        }

        $domain = substr(strrchr((string) $value, '@'), 1);

        if (! $domain) {
            $fail('Please enter a valid email address.');

            return;
        }

        if (! $sponsorId) {
            $fail('Please select a sponsor before entering an email.');
            
            return;
        }

        $sponsor = Sponsor::with('domainNames')->find($sponsorId);
        if (! $sponsor) {
            $fail('The selected sponsor could not be found.');

            return;
        }

        $exists = $sponsor->domainNames()
            ->where('domain_name', $domain)
            ->exists();

        if (! $exists) {
            $fail("The email domain '{$domain}' is not allowed for {$sponsor->name}.");
        }
    }
}
