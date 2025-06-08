<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class DisallowConfusingTripNames implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Str::contains($value, ['due', 'fee', 'pay', 'deposit'], ignoreCase: true)) {
            $fail(
                'Your trip name contains a disallowed word. Please name your trip with the competition or event you'.
                ' are attending, and the year.'
            );
        }
    }
}
