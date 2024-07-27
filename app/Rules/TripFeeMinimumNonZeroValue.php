<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TripFeeMinimumNonZeroValue implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value > 0 && $value < config('travelpolicy.minimum_trip_fee')) {
            $fail('Trip fee must be either $0 or at least $'.config('travelpolicy.minimum_trip_fee'));
        }
    }
}
