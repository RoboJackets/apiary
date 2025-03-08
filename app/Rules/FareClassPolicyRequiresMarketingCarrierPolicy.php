<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FareClassPolicyRequiresMarketingCarrierPolicy implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            $fail('Internal error validating policy');

            return;
        }

        if ($decoded['delta'] === false && $decoded['fare_class'] === true) {
            $fail('Fare class rule must also be disabled if marketing carrier rule is disabled');
        }
    }
}
