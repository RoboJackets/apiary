<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Travel;
use App\Nova\Airport;
use App\Rules\FareClassPolicyRequiresMarketingCarrierPolicy;
use App\Rules\MatrixItineraryBusinessPolicy;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Http\Requests\NovaRequest;

class MatrixAirfareSearch extends Action
{
    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Search';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'This tool applies pre-built filters to Matrix searches. You can further adjust filters on '.
        'the Matrix search page if needed.';

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '4xl';

    private const DATE_FLEXIBILITY_LABELS = [
        '0' => 'This day only',
        '10' => 'Or day before',
        '1' => 'Or day after',
        '11' => '+/- 1 day',
        '22' => '+/- 2 days',
    ];

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Travel>  $models
     *
     * @phan-suppress PhanTypeArraySuspicious
     */
    public function handle(ActionFields $fields, Collection $models): ActionResponse
    {
        $extension_codes = [];

        if ($fields->policy_filters['coach'] === true) {
            $extension_codes[] = '+CABIN 3';
        }

        if ($fields->policy_filters['fare_class'] === true) {
            $extension_codes[] = 'F bc=Y|bc=B|bc=M|bc=W|bc=S|bc=H|bc=Q|bc=K|bc=L|bc=U|bc=T|bc=X|bc=V';
        }

        if ($fields->policy_filters['codeshare'] === true) {
            $extension_codes[] = '-CODESHARE';
        }

        $matrix_payload = [
            'type' => 'round-trip',
            'options' => [
                'allowAirportChanges' => 'true',
                'showOnlyAvailable' => 'true',
                'currency' => [
                    'displayName' => 'United States Dollar (USD)',
                    'code' => 'USD',
                ],
                'salesCity' => [
                    'code' => 'ATL',
                    'name' => 'Atlanta',
                ],
                'cabin' => 'COACH',
                'stops' => $fields->policy_filters['nonstop'] === true ? '0' : '-1',
                'extraStops' => $fields->policy_filters['nonstop'] === true ? '0' : '-1',
            ],
            'pax' => [
                'adults' => $fields->passenger_count,
            ],
            'slices' => [
                [
                    'origin' => collect(json_decode(request()->origin_airports, true))
                        ->map(static fn (array $airport): string => $airport['value'])
                        ->toArray(),
                    'dest' => collect(json_decode(request()->destination_airports, true))
                        ->map(static fn (array $airport): string => $airport['value'])
                        ->toArray(),
                    'dates' => [
                        'searchDateType' => 'specific',
                        'departureDate' => $fields->outbound_date,
                        'departureDateType' => 'depart',
                        'departureDateModifier' => $fields->outbound_date_flexibility,
                        'departureDatePreferredTimes' => [],
                        'returnDate' => $fields->return_date,
                        'returnDateType' => 'depart',
                        'returnDateModifier' => $fields->return_date_flexibility,
                        'returnDatePreferredTimes' => [],
                    ],
                    'ext' => implode(';', $extension_codes),
                    'extRet' => implode(';', $extension_codes),
                ],
            ],
        ];

        if ($fields->policy_filters['delta'] === true) {
            if ($fields->policy_filters['nonstop'] === true) {
                $matrix_payload['slices'][0]['routing'] = 'N:DL';
                $matrix_payload['slices'][0]['routingRet'] = 'N:DL';
            } else {
                $matrix_payload['slices'][0]['routing'] = 'C:DL+';
                $matrix_payload['slices'][0]['routingRet'] = 'C:DL+';
            }
        }

        return self::openInNewTab(
            'https://matrix.itasoftware.com/search?search='.urlencode(base64_encode(json_encode($matrix_payload)))
        );
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     *
     * @phan-suppress PhanTypeInvalidCallableArraySize
     */
    public function fields(NovaRequest $request): array
    {
        $resourceId = $request->resourceId ?? $request->resources;

        if ($resourceId === null) {
            return [];
        }

        $trip = Travel::whereId($resourceId)->sole();

        return [
            Date::make('Outbound Date')
                ->default(static fn (): string => $trip->departure_date->format('Y-m-d'))
                ->rules('required', 'date', 'after:tomorrow')
                ->required(),

            Select::make('Outbound Date Flexibility')
                ->options(self::DATE_FLEXIBILITY_LABELS)
                ->default(static fn (): string => '0')
                ->rules('required')
                ->required(),

            Date::make('Return Date')
                ->default(static fn (): string => $trip->return_date->format('Y-m-d'))
                ->rules('required', 'date', 'after:outbound_date')
                ->required(),

            Select::make('Return Date Flexibility')
                ->options(self::DATE_FLEXIBILITY_LABELS)
                ->default(static fn (): string => '0')
                ->rules('required')
                ->required(),

            Tag::make('Origin Airports', 'origin_airports', Airport::class)
                ->default(static fn (): array => [['display' => 'ATL', 'value' => 'ATL']])
                ->rules('required')
                ->required(),

            Tag::make('Destination Airports', 'destination_airports', Airport::class)
                ->rules('required')
                ->required(),

            Number::make('Passenger Count')
                ->min(1)
                ->max(9)
                ->rules('required', 'integer', 'min:1', 'max:9')
                ->required(),

            BooleanGroup::make('Policy Filters')
                ->options(MatrixItineraryBusinessPolicy::POLICY_LABELS)
                ->default(static fn (): array => $trip->airfare_policy ?? [])
                ->rules('required', new FareClassPolicyRequiresMarketingCarrierPolicy())
                ->required()
                ->help(
                    'Select policies to apply as search filters. <strong>Note that selected itineraries must still '.
                    'meet the policy configured for the trip.</strong>'
                ),

            Boolean::make('Advanced Controls Enabled')
                ->rules('required', 'accepted')
                ->required()
                ->help(
                    '<strong>You must enable advanced controls in <a href="https://matrix.itasoftware.com">Matrix'.
                    '</a> before clicking Search below</strong>, otherwise policies will not be applied.'
                ),
        ];
    }
}
