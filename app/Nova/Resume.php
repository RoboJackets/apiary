<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Resume as AppModelsResume;
use App\Models\User;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

/**
 * A Nova resource for resumes.
 *
 * @extends \App\Nova\Resource<\App\Models\Resume>
 */
class Resume extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Resume>
     */
    public static $model = AppModelsResume::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'user';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Other';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
        'user',
    ];

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 5;

    /**
     * The number of results to display when searching the resource using Scout.
     *
     * @var int
     */
    public static $scoutSearchResults = 5;

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('User', 'user', User::class)
                ->rules('required')
                ->sortable(),
            Text::make('Last Uploaded', 'updated_at')
                ->rules('required')
                ->sortable(),
        ];
    }
}
