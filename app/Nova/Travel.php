<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed.DisallowedPartiallyKeyed

namespace App\Nova;

use App\Models\Travel as AppModelsTravel;
use App\Nova\Actions\MatrixAirfareSearch;
use App\Nova\Metrics\PaymentReceivedForTravel;
use App\Nova\Metrics\TravelAuthorityRequestReceivedForTravel;
use App\Rules\FareClassPolicyRequiresMarketingCarrierPolicy;
use App\Rules\MatrixItineraryBusinessPolicy;
use App\Util\Matrix;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for travel.
 *
 * @extends \App\Nova\Resource<\App\Models\Travel>
 */
class Travel extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Travel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Travel';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
        'destination',
        'included_with_fee',
        'not_included_with_fee',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'primaryContact',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Event Name', 'name')
                ->sortable()
                ->help('This should typically be the name of the competition followed by the year.')
                ->required()
                ->rules('required', 'max:255')
                ->creationRules('unique:travel,name')
                ->updateRules('unique:travel,name,{{resourceId}}'),

            Text::make('Destination')
                ->sortable()
                ->required()
                ->rules('required', 'max:255'),

            BelongsTo::make('Primary Contact', 'primaryContact', User::class)
                ->withoutTrashed()
                ->searchable(),

            Date::make('Departure Date')
                ->required()
                ->rules('required', 'date', 'before:return_date'),

            Date::make('Return Date')
                ->required()
                ->rules('required', 'date', 'after:departure_date'),

            Currency::make('Fee', 'fee_amount')
                ->sortable()
                ->required()
                ->rules('required', 'integer', 'min:'.config('travelpolicy.minimum_trip_fee'), 'max:1000')
                ->min(config('travelpolicy.minimum_trip_fee'))
                ->help(
                    'The trip fee must be at least '.
                    (config('travelpolicy.minimum_trip_fee_cost_ratio') * 100).
                    '% of the per-person total cost for this trip.'
                )
                ->max(1000),

            Markdown::make('Included with Fee')
                ->required()
                ->rules('required')
                ->help(
                    'Describe what costs will be covered by RoboJackets. Typically, this is limited to registration '
                    .'fees, plane tickets, rental vehicles, and fuel. Ground transportation and lodging may be covered '
                    .'at the discretion of the project manager, president, and treasurer.'
                ),

            Markdown::make('Not Included with Fee')
                ->help(
                    'Describe what costs are anticipated to be covered by members themselves. Typically, this '
                    .'includes any meals not provided by the event, entertainment or leisure activities, lodging in '
                    .'excess of the minimum for the event, visa or passport fees, personal luggage fees, and any item'
                    .' that violates United States or local laws.'
                ),

            Boolean::make('Payment Completion Email Sent')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            Boolean::make('Form Completion Email Sent')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            BooleanGroup::make('Airfare Policy')
                ->options(MatrixItineraryBusinessPolicy::POLICY_LABELS)
                ->default(static function (): array {
                    $default = [];

                    // @phan-suppress-next-line PhanUnusedVariableValueOfForeachWithKey
                    foreach (MatrixItineraryBusinessPolicy::POLICY_LABELS as $flag => $label) {
                        $default[$flag] = true;
                    }

                    return $default;
                })
                ->readonly(static fn (NovaRequest $request): bool => $request->user()->cant('update-airfare-policy'))
                ->required()
                ->rules('required', new FareClassPolicyRequiresMarketingCarrierPolicy())
                ->help(
                    $request->user()->can('update-airfare-policy') ?
                        null :
                        'You do not have permission to change the airfare policy.'
                )
                ->hideFromIndex(),

            new Panel(
                'Travel Authority Request',
                [
                    Boolean::make('TAR Required', 'tar_required')
                        ->help(
                            'Check this box if Travel Authority Requests need to be submitted to the Institute.'
                            .' Each traveler will need to submit one individually, and they will be automatically'
                            .' collected within MyRoboJackets.'
                        )
                        ->hideFromIndex(),

                    BooleanGroup::make('Transportation Mode', 'tar_transportation_mode')
                        ->options(
                            [
                                'state_contract_airline' => 'State Contract Airline',
                                'non_contract_airline' => 'Non-Contract Airline',
                                'personal_automobile' => 'Personal Automobile',
                                'rental_vehicle' => 'Rental Vehicle',
                                'other' => 'Other',
                            ]
                        )->help(
                            'Select all transportation modes that will be used. This will be populated on TAR forms.'
                        )
                        ->hideFromIndex(),

                    Text::make('Itinerary', 'tar_itinerary')
                        ->required()
                        ->rules('required_if:tar_required,1', 'max:255')
                        ->help(
                            'This will be populated on TAR forms.'
                        )
                        ->hideFromIndex(),

                    Text::make('Purpose', 'tar_purpose')
                        ->required()
                        ->rules('required_if:tar_required,1', 'max:255')
                        ->help(
                            'This will be populated on TAR forms.'
                        )
                        ->hideFromIndex(),

                    Currency::make('Airfare Cost', 'tar_airfare')
                        ->required()
                        ->rules('required_if:tar_required,1', 'nullable', 'integer')
                        ->min(0)
                        ->max(10000)
                        ->help(
                            'Enter the estimated airfare cost per person in this field.'
                            .' If you are not traveling by air, enter 0.'
                        )
                        ->hideFromIndex(),

                    Currency::make('Lodging Cost', 'tar_lodging')
                        ->required()
                        ->rules('required_if:tar_required,1', 'nullable', 'integer')
                        ->min(0)
                        ->max(1000)
                        ->help(
                            'Enter the estimated lodging cost per person in this field.'
                            .' If you are not staying overnight, enter 0.'
                        )
                        ->hideFromIndex(),

                    Currency::make('Other Transportation Cost', 'tar_other_trans')
                        ->required()
                        ->rules('required_if:tar_required,1', 'nullable', 'integer')
                        ->min(0)
                        ->max(1000)
                        ->help(
                            'Enter the estimated cost for other transportation per person in this field.'.
                            ' If this is not applicable, enter 0.'
                        )
                        ->hideFromIndex(),

                    Currency::make('Registration Cost', 'tar_registration')
                        ->required()
                        ->rules('required_if:tar_required,1', 'nullable', 'integer')
                        ->min(0)
                        ->max(1000)
                        ->help(
                            'Enter the estimated cost for registration per person in this field.'.
                            ' If this is not applicable, enter 0.'
                        )
                        ->hideFromIndex(),

                    Text::make('Workday Project Number', 'tar_project_number')
                        ->required()
                        ->rules(
                            'required_if:tar_required,1',
                            'nullable',
                            'max:255',
                            'in:CE0339,DE00007513,GTF250000211' // agency, SGA, ME GTF
                        )
                        ->help(
                            'Ask the treasurer for the correct value for this field.'
                        )
                        ->hideFromIndex(),

                    Text::make('Account Code', 'tar_account_code')
                        ->required()
                        ->rules('required_if:tar_required,1', 'nullable', 'digits:6')
                        ->help(
                            'Ask the treasurer for the correct value for this field.'
                        )
                        ->hideFromIndex(),
                ]
            ),

            new Panel(
                'International Travel',
                [
                    Boolean::make('Destination Outside United States', 'is_international')
                        ->help(
                            'Check this box if your destination is outside of the United States.'
                        )
                        ->hideFromIndex(),

                    Text::make('Justification', 'international_travel_justification')
                        ->required()
                        ->rules('required_if:is_international,1', 'nullable')
                        ->help(
                            'Please explain how this travel meets essential travel criteria as defined by Georgia Tech.'
                        )
                        ->hideFromIndex(),

                    Boolean::make('Export-Controlled Technology', 'export_controlled_technology')
                        ->required()
                        ->help(
                            'Do you plan to take any information or technology that is controlled?'
                        )
                        ->hideFromIndex(),

                    Text::make('Export-Controlled Technology Description', 'export_controlled_technology_description')
                        ->required()
                        ->rules('required_if:export_controlled_technology,1', 'nullable')
                        ->help(
                            'If yes, please describe the information or technology.'
                        )
                        ->hideFromIndex(),

                    Boolean::make('Embargoed Destination', 'embargoed_destination')
                        ->required()
                        ->help(
                            'Do you plan to travel to an embargoed destination?'
                        )
                        ->hideFromIndex(),

                    Text::make('Embargoed Destination Description', 'embargoed_countries')
                        ->required()
                        ->rules('required_if:embargoed_destination,1', 'nullable')
                        ->help(
                            'If yes, please list the country or countries.'
                        )
                        ->hideFromIndex(),

                    Boolean::make('Biological Materials', 'biological_materials')
                        ->required()
                        ->help(
                            'Are you taking any biological materials?'
                        )
                        ->hideFromIndex(),

                    Text::make('Biological Materials Description', 'biological_materials_description')
                        ->required()
                        ->rules('required_if:biological_materials,1', 'nullable')
                        ->help(
                            'If yes, please identify the material.'
                        )
                        ->hideFromIndex(),

                    Boolean::make('Equipment', 'equipment')
                        ->required()
                        ->help(
                            'Are you taking any equipment containing work involving foreign national restrictions,'.
                            ' publication restrictions, technology control plans, proprietary information, or '.
                            'specialized encryption software?'
                        )
                        ->hideFromIndex(),

                    Text::make('Equipment Description', 'equipment_description')
                        ->required()
                        ->rules('required_if:equipment,1', 'nullable')
                        ->help(
                            'If yes, please list the equipment.'
                        )
                        ->hideFromIndex(),
                ]
            ),

            HasMany::make('Assignments', 'assignments', TravelAssignment::class),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            (new Actions\DownloadDocuSignForms())
                ->canSee(static fn (Request $request): bool => $request->user()->can('view-docusign-envelopes') ||
                        \App\Models\Travel::where('primary_contact_user_id', $request->user()->id)->exists())
                ->canRun(
                    static fn (NovaRequest $request, AppModelsTravel $travel): bool => $request->user()->can(
                        'view-docusign-envelopes'
                    ) ||
                            $travel->primaryContact->id === $request->user()->id
                ),

            MatrixAirfareSearch::make(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        $cards = [
            (new PaymentReceivedForTravel())->onlyOnDetail(),
        ];

        if ($request->resourceId === null) {
            return [];
        }

        $requires_tar = AppModelsTravel::where('id', $request->resourceId)->sole()->tar_required;

        if ($requires_tar) {
            $cards[] = (new TravelAuthorityRequestReceivedForTravel())->onlyOnDetail();
        }

        return $cards;
    }

    /**
     * Handle any post-validation processing.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    protected static function afterValidation(NovaRequest $request, $validator): void
    {
        $totalCost = $request->tar_lodging + $request->tar_registration;

        if ($totalCost === 0) {
            return;
        }

        if ($request->resourceId !== null) {
            $trip = \App\Models\Travel::where('id', '=', $request->resourceId)->sole();

            $airfareCost = $trip->assignments->reduce(
                static function (?float $carry, \App\Models\TravelAssignment $assignment): ?float {
                    // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
                    $thisAirfareCost = Matrix::getHighestDisplayPrice(json_encode($assignment->matrix_itinerary));

                    if ($thisAirfareCost !== null && $carry !== null && $thisAirfareCost > $carry) {
                        return $thisAirfareCost;
                    } elseif ($thisAirfareCost !== null && $carry === null) {
                        return $thisAirfareCost;
                    } else {
                        return $carry;
                    }
                }
            );

            if ($airfareCost !== null && $airfareCost > 0) {
                $totalCost += $airfareCost;
            }
        }

        $feeAmount = $request->fee_amount;

        if ($feeAmount / $totalCost < config('travelpolicy.minimum_trip_fee_cost_ratio')) {
            $validator->errors()->add(
                'fee_amount',
                'Trip fee must be at least '.
                (config('travelpolicy.minimum_trip_fee_cost_ratio') * 100).
                '% of the per-person cost for this trip.'
            );
        }
    }

    /**
     * Only show travel scheduled for the future for relatable queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Travel>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Travel>
     */
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        if ($request->current !== null) {
            return $query->where('id', '=', $request->current);
        }

        if ($request->is('nova-api/travel-assignments/*')) {
            return $query->whereDate('departure_date', '>=', Carbon::now());
        }

        return $query;
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): string
    {
        return $this->destination.' | '.$this->departure_date->format('F Y');
    }

    /**
     * Register a callback to be called after the resource is created.
     */
    public static function afterCreate(NovaRequest $request, Model $model): void
    {
        if ($model->airfare_policy !== null) {
            return;
        }

        $default = [];

        // @phan-suppress-next-line PhanUnusedVariableValueOfForeachWithKey
        foreach (MatrixItineraryBusinessPolicy::POLICY_LABELS as $flag => $label) {
            $default[$flag] = true;
        }

        $model->airfare_policy = $default;
        $model->save();
    }
}
