<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for webhook calls.
 *
 * @extends \App\Nova\Resource<\Spatie\WebhookClient\Models\WebhookCall>
 *
 * @phan-suppress PhanUnreferencedClass
 */
class WebhookCall extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \Spatie\WebhookClient\Models\WebhookCall::class;

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

            Text::make('Name'),

            URL::make('URL'),

            Code::make('Payload')
                ->json(),

            Text::make('Exception')
                ->onlyOnDetail(),

            new Panel(
                'Timestamps',
                [
                    DateTime::make('Created', 'created_at')
                        ->onlyOnDetail(),

                    DateTime::make('Last Updated', 'updated_at')
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
