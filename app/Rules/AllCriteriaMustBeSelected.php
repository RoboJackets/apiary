<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllCriteriaMustBeSelected implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            $fail('Internal error validating review criteria');

            return;
        }

        if (in_array(false, $decoded, true)) {
            $fail('All review criteria must be met');
        }
    }
}
