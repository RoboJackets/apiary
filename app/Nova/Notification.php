<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for Nova notifications.
 *
 * @extends \App\Nova\Resource<\Laravel\Nova\Notifications\Notification>
 */
class Notification extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Laravel\Nova\Notifications\Notification::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
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
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(),

            Text::make('Type')
                ->sortable()
                ->filterable(),

            MorphTo::make('User', 'notifiable'),

            Code::make('Data')
                ->json(),

            Panel::make(
                'Timestamps',
                [
                    DateTime::make('Created At')
                        ->onlyOnDetail(),

                    DateTime::make('Updated At')
                        ->onlyOnDetail(),

                    DateTime::make('Read At')
                        ->onlyOnDetail(),

                    DateTime::make('Deleted At')
                        ->onlyOnDetail(),
                ]
            ),
        ];
    }

    #[\Override]
    public static function searchable(): bool
    {
        return false;
    }
}
