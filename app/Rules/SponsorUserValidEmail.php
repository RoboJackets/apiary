<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Sponsor;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SponsorUserValidEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sponsorId = request()->input('company');

        $email = (string) $value;
        $domain = substr(strrchr($email, '@'), 1);

        if (empty($domain)) {
            $fail('Please enter a valid email address.');

            return;
        }

        if (empty($sponsorId)) {
            $fail('Please select a sponsor before entering an email.');

            return;
        }

        $sponsor = Sponsor::with('domainNames')->find($sponsorId);
        if (empty($sponsor)) {
            $fail('The selected sponsor could not be found.');

            return;
        }

        $exists = $sponsor->domainNames()
            ->where('domain_name', $domain)
            ->exists();

        if (! $exists) {
            $fail('The email domain "'.$domain.'" is not allowed for '.$sponsor->name.'.');
        }
    }
}
