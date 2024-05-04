<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * A Nova resource for access cards.
 *
 * @extends \App\Nova\Resource<\App\Models\AccessCard>
 */
class AccessCard extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\AccessCard::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'access_card_number';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'access_card_number',
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
            ID::make('Access Card Number', 'access_card_number')->sortable(),

            BelongsTo::make('User'),

            HasMany::make('Attendance'),
        ];
    }
}
