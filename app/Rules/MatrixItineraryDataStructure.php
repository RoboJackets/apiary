<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class MatrixItineraryDataStructure implements ValidationRule
{
    private const RULES = [
        'passengerCount' => [
            'required',
            'integer',
            'min:1',
            'max:9',
        ],
        'displayTotal' => [
            'required',
            'string',
            'regex:/^USD[0-9]{1,5}\.[0-9]{2}$/',
        ],
        'itinerary' => [
            'required',
            'array',
        ],
        'itinerary.slices' => [
            'required',
            'array',
        ],
        'itinerary.slices.*' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.stopCount' => [
            'required',
            'integer',
        ],
        'itinerary.slices.*.origin' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.origin.code' => [
            'required',
            'string',
            'size:3',
        ],
        'itinerary.slices.*.origin.city' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.origin.city.name' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.destination' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.destination.code' => [
            'required',
            'string',
            'size:3',
        ],
        'itinerary.slices.*.destination.city' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.destination.city.name' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.origin' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.origin.code' => [
            'required',
            'string',
            'size:3',
        ],
        'itinerary.slices.*.segments.*.origin.city' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.origin.city.name' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.destination' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.destination.code' => [
            'required',
            'string',
            'size:3',
        ],
        'itinerary.slices.*.segments.*.destination.city' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.destination.city.name' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.legs' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.legs.*' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.legs.*.origin' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.legs.*.origin.code' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.legs.*.destination' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.legs.*.destination.code' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.legs.*.arrival' => [
            'required',
            'date',
        ],
        'itinerary.slices.*.segments.*.legs.*.departure' => [
            'required',
            'date',
        ],
        'itinerary.slices.*.segments.*.legs.*.aircraft' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.legs.*.aircraft.shortName' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.arrival' => [
            'required',
            'date',
        ],
        'itinerary.slices.*.segments.*.departure' => [
            'required',
            'date',
        ],
        'itinerary.slices.*.arrival' => [
            'required',
            'date',
        ],
        'itinerary.slices.*.departure' => [
            'required',
            'date',
        ],
        'itinerary.slices.*.segments.*.flight' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.flight.number' => [
            'required',
            'integer',
        ],
        'itinerary.slices.*.segments.*.bookingInfos.*' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.bookingInfos.*.cabin' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.bookingInfos.*.bookingCode' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.carrier' => [
            'required',
            'array',
        ],
        'itinerary.slices.*.segments.*.carrier.code' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.carrier.shortName' => [
            'required',
            'string',
        ],
        'itinerary.slices.*.segments.*.codeshare' => [
            'sometimes',
            'boolean',
        ],
        'itinerary.slices.*.segments.*.ext' => [
            'sometimes',
            'array',
        ],
        'itinerary.slices.*.segments.*.ext.operationalDisclosure' => [
            'sometimes',
            'string',
        ],
        'pricings' => [
            'required',
            'array',
        ],
        'pricings.*' => [
            'required',
            'array',
        ],
        'pricings.*.displayPrice' => [
            'required',
            'string',
            'regex:/^USD[0-9]{1,5}\.[0-9]{2}$/',
        ],
    ];

    private const MESSAGES = [
        'displayTotal' => 'Airfare must be priced in USD.',
    ];

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

        $validator = Validator::make($decoded, self::RULES, self::MESSAGES);

        if ($validator->fails()) {
            $fail($validator->messages()->first());
        }
    }
}
