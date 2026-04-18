<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Resume as AppModelsResume;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @extends \App\Nova\Resource<\App\Models\Resume>
 */
class Resume extends Resource
{
    /**
     * @var class-string<\App\Models\Resume>
     */
    public static $model = AppModelsResume::class;

    /**
     * @var string
     */
    public static $title = 'filename';

    /**
     * @var array<int, string>
     */
    public static $search = [
        'id',
        'filepath',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('User')
                ->searchable()
                ->required(),

            File::make('Resume', 'filepath')
                ->disk('local')
                ->path('resumes')
                ->creationRules('required', 'file')
                ->updateRules('nullable', 'file'),

            DateTime::make('Last Uploaded At')
                ->exceptOnForms(),

            DateTime::make('Created At')
                ->onlyOnDetail(),

            DateTime::make('Updated At')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Cards.
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Filters.
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Lenses.
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Actions.
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
