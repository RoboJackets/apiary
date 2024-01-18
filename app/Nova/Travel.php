<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition.RequiredSingleLineCondition

namespace App\Nova;

use App\Models\Travel as AppModelsTravel;
use App\Notifications\Nova\LinkDocuSignAccount;
use App\Nova\Actions\MatrixAirfareSearch;
use App\Nova\Metrics\PaymentReceivedForTravel;
use App\Nova\Metrics\TravelAuthorityRequestReceivedForTravel;
use App\Rules\FareClassPolicyRequiresMarketingCarrierPolicy;
use App\Rules\MatrixItineraryBusinessPolicy;
use App\Util\DepartmentNumbers;
use App\Util\DocuSign;
use App\Util\Matrix;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Select;
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
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Trips';
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'trips';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @phan-suppress PhanTypeInvalidCallableArraySize
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Trip Name', 'name')
                ->help(view('nova.help.travel.tripname')->render())
                ->sortable()
                ->required()
                ->rules('required', 'min:5', 'max:255')
                ->creationRules('unique:travel,name')
                ->updateRules('unique:travel,name,{{resourceId}}'),

            Text::make('Destination')
                ->sortable()
                ->required()
                ->rules('required', 'min:3', 'max:60')
                ->maxlength(60)
                ->enforceMaxlength(),

            BelongsTo::make('Primary Contact', 'primaryContact', User::class)
                ->help(view('nova.help.travel.primarycontact')->render())
                ->sortable()
                ->required()
                ->rules('required')
                ->searchable()
                ->withoutTrashed(),

            Date::make('Departure Date')
                ->sortable()
                ->required()
                ->rules('required', 'date', 'before:return_date'),

            Date::make('Return Date')
                ->sortable()
                ->required()
                ->rules('required', 'date', 'after:departure_date'),

            Boolean::make('Payment Completion Email Sent')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Boolean::make('Form Completion Email Sent')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Panel::make('Trip Fee', [
                Currency::make('Fee Amount', 'fee_amount')
                    ->help(view('nova.help.travel.feeamount')->render())
                    ->sortable()
                    ->required()
                    ->rules(
                        'required',
                        'integer',
                        'min:'.config('travelpolicy.minimum_trip_fee'),
                        'max:'.config('travelpolicy.maximum_trip_fee')
                    )
                    ->min(config('travelpolicy.minimum_trip_fee'))
                    ->max(config('travelpolicy.maximum_trip_fee')),

                Text::make('Costs Paid by RoboJackets', 'included_with_fee')
                    ->required()
                    ->rules('required')
                    ->help(view('nova.help.travel.includedwithfee')->render())
                    ->hideFromIndex(),

                Text::make('Costs Paid by Travelers', 'not_included_with_fee')
                    ->required()
                    ->rules('required')
                    ->help(view('nova.help.travel.notincludedwithfee')->render())
                    ->hideFromIndex(),
            ]),

            Panel::make('Forms', [
                BooleanGroup::make('Collect Forms', 'forms')
                    ->options(\App\Models\Travel::FORM_LABELS)
                    ->required(false)
                    ->rules('required', 'json')
                    ->showOnDetail(fn (): bool => $this->forms !== null && in_array(true, $this->forms, true))
                    ->hideFromIndex()
                    ->help('If you\'re not sure which forms you need to collect, please check with the treasurer.'),

                Text::make('Trip Purpose', 'tar_purpose')
                    ->dependsOn(
                        ['forms'],
                        static function (Text $field, NovaRequest $request, FormData $formData): void {
                            if (
                                self::showFieldOnForms(
                                    $formData,
                                    \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY,
                                    \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                                )
                            ) {
                                $field->show()
                                    ->rules('required', 'min:20', 'max:60');
                            }
                        }
                    )
                    ->showOnDetail(
                        fn (): bool => $this->showFieldOnDetail(
                            \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY,
                            \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                        )
                    )
                    ->required()
                    ->hide()
                    ->maxlength(60)
                    ->enforceMaxlength()
                    ->rules('sometimes')
                    ->hideFromIndex(),

                Heading::make('Trip Costs')
                    ->dependsOn(
                        ['forms'],
                        static function (Heading $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)) {
                                $field->show();
                            }
                        }
                    )
                    ->hide()
                    ->showOnDetail(fn (): bool => $this->showFieldOnDetail(
                        \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY
                    )),

                Text::make('Hotel Name')
                    ->required()
                    ->dependsOn(
                        ['forms'],
                        static function (Text $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)) {
                                $field->show()
                                    ->required()
                                    ->rules('max:30');
                            }
                        }
                    )
                    ->rules('sometimes')
                    ->hide()
                    ->showOnDetail(
                        fn (): bool => $this->showFieldOnDetail(\App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)
                    )
                    ->help('If you are not staying overnight, leave this field blank.')
                    ->maxlength(30)
                    ->enforceMaxlength()
                    ->hideFromIndex(),

                Currency::make('Hotel Cost Per Person', 'tar_lodging')
                    ->dependsOn(
                        ['forms'],
                        static function (Currency $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)) {
                                $field->show()
                                    // GSA FY2024 non-standard area max is $485 - guess where
                                    ->rules('required', 'integer', 'min:0', 'max:500');
                            }
                        }
                    )
                    ->hide()
                    ->showOnDetail(
                        fn (): bool => $this->showFieldOnDetail(\App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)
                    )
                    ->required()
                    ->rules('sometimes')
                    ->min(0)
                    ->max(500)
                    ->help(
                        'Enter the estimated hotel cost per person in this field.'
                        .' If you are not staying overnight, enter 0.'
                    )
                    ->hideFromIndex(),

                Currency::make('Registration Cost Per Person', 'tar_registration')
                    ->dependsOn(
                        ['forms'],
                        static function (Currency $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)) {
                                $field->show()
                                    ->rules('required', 'min:0', 'max:1000');
                            }
                        }
                    )
                    ->required()
                    ->hide()
                    ->showOnDetail(
                        fn (): bool => $this->showFieldOnDetail(\App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)
                    )
                    ->rules('sometimes')
                    ->min(0)
                    ->max(1000)
                    ->help(
                        'Enter the estimated cost for registration per person in this field.'.
                        ' If registration is free, enter 0.'
                    )
                    ->hideFromIndex(),

                Currency::make('Car Rental Cost Per Person', 'car_rental_cost')
                    ->dependsOn(
                        ['forms'],
                        static function (Currency $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)) {
                                $field->show()
                                    ->rules('required', 'integer', 'min:0', 'max:1000');
                            }
                        }
                    )
                    ->hide()
                    ->showOnDetail(
                        fn (): bool => $this->showFieldOnDetail(\App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)
                    )
                    ->required()
                    ->help(
                        'Enter the estimated cost for car rental per person in this field. If you are not renting '.
                        'cars, enter 0.'
                    )
                    ->rules('sometimes')
                    ->min(0)
                    ->max(1000)
                    ->hideFromIndex(),

                Currency::make('Meal Per Diem')
                    ->dependsOn(
                        ['forms'],
                        static function (Currency $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)) {
                                $field->show()
                                    // GSA FY2024 non-standard area max is $79
                                    ->rules('required', 'integer', 'min:0', 'max:100');
                            }
                        }
                    )
                    ->hide()
                    ->showOnDetail(
                        fn (): bool => $this->showFieldOnDetail(\App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY)
                    )
                    ->help('Enter the meal per diem allowance per person. This is generally $0.')
                    ->required()
                    ->rules('sometimes')
                    ->min(0)
                    ->max(100)
                    ->hideFromIndex(),

                Heading::make('Accounting Information')
                    ->dependsOn(
                        ['forms'],
                        static function (Heading $field, NovaRequest $request, FormData $formData): void {
                            if (
                                self::showFieldOnForms(
                                    $formData,
                                    \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY,
                                    \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                                )
                            ) {
                                $field->show();
                            }
                        }
                    )
                    ->hide()
                    ->showOnDetail(fn (): bool => $this->showFieldOnDetail(
                        \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY,
                        \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                    )),

                Text::make('Workday Account Number', 'tar_project_number')
                    ->dependsOn(
                        ['forms'],
                        static function (Text $field, NovaRequest $request, FormData $formData): void {
                            if (
                                self::showFieldOnForms(
                                    $formData,
                                    \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY,
                                    \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                                )
                            ) {
                                $field->show()
                                    ->rules(
                                        'required',
                                        'max:255',
                                        'in:CE0339,DE00007513,GTF250000211' // agency, SGA, ME GTF
                                    );
                            }
                        }
                    )
                    ->showOnDetail(fn (): bool => $this->showFieldOnDetail(
                        \App\Models\Travel::TRAVEL_INFORMATION_FORM_KEY,
                        \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                    ))
                    ->required()
                    ->hide()
                    ->rules('sometimes')
                    ->help(
                        'Ask the treasurer for the correct value for this field.'
                    )
                    ->hideFromIndex(),

                Select::make('Department', 'department_number')
                    ->options(DepartmentNumbers::DESCRIPTIONS)
                    ->displayUsingLabels()
                    ->searchable()
                    ->dependsOn(
                        ['forms'],
                        static function (Select $field, NovaRequest $request, FormData $formData): void {
                            if (self::showFieldOnForms($formData, \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY)) {
                                $field->show()
                                    ->required()
                                    ->rules(
                                        'required',
                                        'size:3',
                                        'in:'.implode(',', array_keys(DepartmentNumbers::DESCRIPTIONS))
                                    );
                            }
                        }
                    )
                    ->showOnDetail(fn (): bool => $this->showFieldOnDetail(
                        \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                    ))
                    ->required()
                    ->hide()
                    ->hideFromIndex()
                    ->help('Select the department responsible for <strong>airfare</strong> costs.'),
            ]),

            Panel::make(
                'Airfare',
                [
                    BooleanGroup::make('Airfare Policy')
                        ->options(MatrixItineraryBusinessPolicy::POLICY_LABELS)
                        ->dependsOn(
                            ['forms'],
                            static function (BooleanGroup $field, NovaRequest $request, FormData $formData): void {
                                if (self::showFieldOnForms($formData, \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY)) {
                                    $field->show()
                                        ->required()
                                        ->rules(
                                            'required',
                                            'json',
                                            new FareClassPolicyRequiresMarketingCarrierPolicy()
                                        );
                                }
                            }
                        )
                        ->showOnDetail(fn (): bool => $this->showFieldOnDetail(
                            \App\Models\Travel::AIRFARE_REQUEST_FORM_KEY
                        ))
                        ->default(static function (): array {
                            $default = [];

                            // @phan-suppress-next-line PhanUnusedVariableValueOfForeachWithKey
                            foreach (MatrixItineraryBusinessPolicy::POLICY_LABELS as $flag => $label) {
                                $default[$flag] = true;
                            }

                            return $default;
                        })
                        ->readonly(static fn (NovaRequest $request): bool => $request->user()->cant(
                            'update-airfare-policy'
                        ))
                        ->hide()
                        ->help(view('nova.help.travel.airfarepolicy')->render())
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

        $requires_tar = AppModelsTravel::where('id', $request->resourceId)->sole()->needs_docusign;

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
        // require trip name to include the departure or return year
        if ($request->name !== null && ($request->departure_date !== null || $request->return_date !== null)) {
            $departure_year = null;
            $return_year = null;

            if ($request->departure_date !== null) {
                $departure_year = substr($request->departure_date, 0, 4);
            }

            if ($request->return_date !== null) {
                $return_year = substr($request->return_date, 0, 4);
            }

            if (
                ! ($departure_year !== null && str_contains($request->name, $departure_year)) &&
                ! ($return_year !== null && str_contains($request->name, $return_year))
            ) {
                $validator->errors()->add('name', 'The trip name must include the year of the event.');
            }
        }

        // ensure primary contact has valid DocuSign credentials
        if (
            $request->primaryContact !== null &&
            $request->forms !== null &&
            in_array(true, json_decode($request->forms, true), true)
        ) {
            $primary_contact_user = \App\Models\User::where('id', '=', $request->primaryContact)->sole();

            if (DocuSign::getApiClientForUser($primary_contact_user) === null) {
                if (intval($request->primaryContact) === $request->user()->id) {
                    if (
                        $primary_contact_user
                            ->novaNotifications()
                            ->where('type', '=', LinkDocuSignAccount::class)
                            ->doesntExist()
                    ) {
                        $primary_contact_user->notifyNow(new LinkDocuSignAccount($request->name ?? 'your trip'));

                        DB::commit();
                    }

                    $validator->errors()->add(
                        'primaryContact',
                        'Your DocuSign account needs to be linked with '.config('app.name').
                        ' to send forms. Click the bell icon in the top-right for instructions.'
                    );
                } else {
                    if (
                        $primary_contact_user
                            ->novaNotifications()
                            ->where('type', '=', LinkDocuSignAccount::class)
                            ->doesntExist()
                    ) {
                        $primary_contact_user->notifyNow(new LinkDocuSignAccount($request->name ?? 'trips'));

                        DB::commit();
                    }

                    $validator->errors()->add(
                        'primaryContact',
                        $primary_contact_user->preferred_first_name.'\'s DocuSign account needs to be linked with '.
                        config('app.name').
                        ' to send forms. Ask them to check their notifications here in the admin site '.
                        '(bell icon in top right) for instructions.'
                    );
                }
            }
        }

        // require hotel name to be provided if hotel cost is >0
        if ($request->tar_lodging > 0 && $request->hotel_name === null) {
            $validator->errors()->add(
                'hotel_name',
                'The hotel name is required if the hotel cost per person is greater than $0.'
            );
        }

        // validate account and department make sense
        if ($request->tar_project_number !== null && $request->department_number !== null) {
            if ($request->tar_project_number === 'GTF250000211' && $request->department_number !== '250') {
                $validator->errors()->add(
                    'department_number',
                    'The selected department and Workday account numbers do not match.'
                );
            }
        }

        // deliberately not including meal per diem rate because that seems weird
        $totalCost = intval($request->tar_lodging) +
            intval($request->tar_registration) +
            intval($request->car_rental_cost);

        if ($request->resourceId !== null) {
            $trip = \App\Models\Travel::where('id', '=', $request->resourceId)->sole();

            $airfareCost = $trip->assignments->reduce(
                static function (?float $carry, \App\Models\TravelAssignment $assignment): ?float {
                    $thisAirfareCost = Matrix::getHighestDisplayPrice($assignment->matrix_itinerary);

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

        $feeAmount = intval($request->fee_amount);

        if ($totalCost === 0) {
            return;
        }

        if ($feeAmount / $totalCost < config('travelpolicy.minimum_trip_fee_cost_ratio')) {
            $validator->errors()->add(
                'fee_amount',
                trim(view('nova.help.travel.feevalidation', ['totalCost' => $totalCost])->render())
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
            return $query->where('id', '=', $request->current)->orWhereDate('departure_date', '>=', Carbon::now());
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

    private static function showFieldOnForms(FormData $formData, string ...$fieldRequiredForForms): bool
    {
        $json = $formData->json('forms');

        if (! is_array($json)) {
            return false;
        }

        foreach ($fieldRequiredForForms as $form) {
            if (array_key_exists($form, $json) && $json[$form] === true) {
                return true;
            }
        }

        return false;
    }

    private function showFieldOnDetail(string ...$fieldRequiredForForms): bool
    {
        if ($this->forms === null) {
            return false;
        }

        foreach ($fieldRequiredForForms as $form) {
            if (array_key_exists($form, $this->forms) && $this->forms[$form] === true) {
                return true;
            }
        }

        return false;
    }
}
