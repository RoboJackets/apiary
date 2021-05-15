<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\Text;

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
        'documents_required',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Name')
                ->sortable()
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
                ->rules('required'),

            Date::make('Return Date')
                ->required()
                ->rules('required', 'date', 'after:departure_date'),

            Currency::make('Fee', 'fee_amount')
                ->sortable()
                ->required()
                ->rules('required', 'integer')
                ->min(1)
                ->max(1000),

            Markdown::make('Included with Fee')
                ->required()
                ->rules('required')
                ->help('Describe what costs will be covered by RoboJackets.'),

            Markdown::make('Not Included with Fee')
                ->help('Describe what costs are anticipated to be covered by members themselves.'),

            Markdown::make('Documents Required')
                ->help('Describe what documents will be required to travel.'),

            HasMany::make('Assignments', 'assignments', TravelAssignment::class),

            self::metadataPanel(),
        ];
    }
}
