<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class MatrixItineraryBusinessPolicy implements ValidationRule
{
    public const POLICY_LABELS = [
        'max_slices' => 'Itineraries must be one-way or round-trip',
        'nonstop' => 'Segments must be single direct non-stop flights',
        'origin_atlanta' => 'First flight must originate from Atlanta',
        'coach' => 'Fares must be in coach cabin',
        'delta' => 'Fares must be marketed by Delta',
        'fare_class' => 'Fares must be in classes Y, B, M, W, S, H, Q, K, L, U, T, X, or V',
        'codeshare' => 'Flights must be operated by marketing carrier',
    ];

    private const POLICY_RULES = [
        'round_trip' => [
            'itinerary.slices' => [
                'required',
                'array',
                'max:2',
            ],
        ],
        'nonstop' => [
            'itinerary.slices.*.stopCount' => [
                'required',
                'integer',
                'max:0',
            ],
            'itinerary.slices.*.segments.*' => [
                'required',
                'array',
                'size:1',
            ],
            'itinerary.slices.*.segments.*.legs' => [
                'required',
                'array',
                'size:1',
            ],
            'itinerary.slices.*.segments' => [
                'required',
                'array',
                'size:1',
            ],
        ],
        'origin_atlanta' => [
            'itinerary.slices.0.origin.code' => [
                'required',
                'string',
                'size:3',
                'in:ATL',
            ],
            'itinerary.slices.0.segments.0.origin.code' => [
                'required',
                'string',
                'size:3',
                'in:ATL',
            ],
            'itinerary.slices.0.segments.0.legs.0.origin.code' => [
                'required',
                'string',
                'size:3',
                'in:ATL',
            ],
        ],
        'coach' => [
            'itinerary.slices.*.segments.*.bookingInfos.*.cabin' => [
                'required',
                'string',
                'in:COACH',
            ],
        ],
        'fare_class' => [
            'itinerary.slices.*.segments.*.bookingInfos.*.bookingCode' => [
                'required',
                'string',
                'in:Y,B,M,W,S,H,Q,K,L,U,T,X,V',
            ],
        ],
        'delta' => [
            'itinerary.slices.*.segments.*.carrier.code' => [
                'required',
                'string',
                'in:DL',
            ],
        ],
        'codeshare' => [
            'itinerary.slices.*.segments.*.codeshare' => [
                'prohibited',
            ],
        ],
    ];

    /**
     * Rules that have been enabled for a given trip.
     *
     * @var array<string,array<string>>
     */
    private array $enabledRules = [];

    /**
     * Messages for all rules.
     *
     * @var array<string,string>
     */
    private array $messages = [];

    /**
     * Construct a business policy validator for a specific trip.
     *
     * @param  array<string,bool>  $policyToggles  policies enabled for a specific trip
     */
    public function __construct(array $policyToggles)
    {
        foreach (self::POLICY_RULES as $policy => $ruleset) {
            if (array_key_exists($policy, $policyToggles) && $policyToggles[$policy] === true) {
                $this->enabledRules = array_merge($this->enabledRules, $ruleset);
            }

            // @phan-suppress-next-line PhanUnusedVariableValueOfForeachWithKey
            foreach ($ruleset as $attribute => $rules) {
                $this->messages[$attribute] = self::POLICY_LABELS[$policy];
            }
        }
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            $fail('Enter an itinerary in Matrix JSON format.');
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            $fail('Enter an itinerary in Matrix JSON format.');

            return;
        }

        $validator = Validator::make($decoded, $this->enabledRules, $this->messages);

        if ($validator->fails()) {
            $fail($validator->messages()->first());
        }
    }
}
