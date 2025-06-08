<?php

declare(strict_types=1);

namespace App\Nova\Actions;

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
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

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
     * Indicates if the action can be run without any models.
     *
     * @var bool
     */
    public $standalone = true;

    /**
     * The size of the modal. Can be "sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "5xl", "6xl", "7xl".
     *
     * @var string
     */
    public $modalSize = '4xl';

    private const array DATE_FLEXIBILITY_LABELS = [
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
            // BC is short for Booking Code, also known as Fare Class
            // Fare classes pulled from https://www.delta.com/us/en/skymiles/how-to-earn-miles/exception-fares
            // This filters for any available fares in main cabin except for basic economy
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

        return ActionResponse::openInNewTab(
            'https://matrix.itasoftware.com/search?search='.urlencode(base64_encode(json_encode($matrix_payload)))
        );
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        $suggestedDefaultDestinationAirport = null;

        if ($this->resource !== null) {
            $results = \App\Models\Airport::search($this->resource->destination)->get();

            if ($results->count() > 0) {
                $suggestedDefaultDestinationAirport = [
                    [
                        'display' => $results->first()->iata,
                        'value' => $results->first()->iata,
                    ],
                ];
            }
        }

        return [
            Date::make('Outbound Date')
                ->default(fn (): ?string => $this->resource?->departure_date?->format('Y-m-d'))
                ->rules('required', 'date', 'after:tomorrow', 'before:+1 year')
                ->required(),

            Select::make('Outbound Date Flexibility')
                ->options(self::DATE_FLEXIBILITY_LABELS)
                ->default(static fn (): string => '0')
                ->rules('required')
                ->required(),

            Date::make('Return Date')
                ->default(fn (): ?string => $this->resource?->return_date?->format('Y-m-d'))
                ->rules('required', 'date', 'after:tomorrow', 'after:outbound_date', 'before:+1 year')
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
                ->default(static fn (): ?array => $suggestedDefaultDestinationAirport)
                ->rules('required')
                ->required(),

            Number::make('Passenger Count')
                ->min(1)
                ->max(9)
                ->rules('required', 'integer', 'min:1', 'max:9')
                ->required(),

            BooleanGroup::make('Policy Filters')
                ->options(MatrixItineraryBusinessPolicy::POLICY_LABELS)
                ->default(fn (): array => $this->resource?->airfare_policy ?? [])
                ->rules('required', new FareClassPolicyRequiresMarketingCarrierPolicy())
                ->required()
                ->help(view('nova.matrixpolicyhelp')->render()),

            Boolean::make('Advanced Controls Enabled')
                ->rules('required', 'accepted')
                ->required()
                ->help(view('nova.matrixadvancedcontrolshelp')->render()),
        ];
    }
}
