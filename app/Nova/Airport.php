<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Country;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for airports.
 *
 * @extends \App\Nova\Resource<\App\Models\Airport>
 */
class Airport extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Airport::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'iata';

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
        'iata',
        'name',
        'city',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('IATA Code', 'iata')
                ->sortable(),

            Text::make('Name')
                ->sortable(),

            Text::make('City')
                ->sortable(),

            Text::make('State')
                ->sortable(),

            Country::make('Country')
                ->sortable(),
        ];
    }

    /**
     * Determine if this resource is available for navigation.
     */
    public static function availableForNavigation(Request $request): bool
    {
        return $request->user()->hasRole('admin');
    }
}
