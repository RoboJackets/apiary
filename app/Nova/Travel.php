<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Travel as AppModelsTravel;
use App\Nova\Metrics\PaymentReceivedForTravel;
use App\Nova\Metrics\TravelAuthorityRequestReceivedForTravel;
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
                ->rules('required', 'integer')
                ->min(10)
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

            Boolean::make('Completion Email Sent')
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

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
                        ->max(1000)
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
                        self::where('primary_contact_user_id', $request->user()->id)->exists())
                ->canRun(
                    static fn (NovaRequest $request, AppModelsTravel $travel): bool => $request->user()->can(
                        'view-docusign-envelopes'
                    ) ||
                            $travel->primaryContact->id === $request->user()->id
                ),
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
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): string
    {
        return $this->destination.' | '.$this->departure_date->format('F Y');
    }
}
