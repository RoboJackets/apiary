<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Metrics\ActiveStudentsInMajor;
use App\Nova\Metrics\MajorsMissingDisplayNames;
use App\Nova\Metrics\StudentsInMajor;
use App\Nova\Metrics\TotalMajors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Text;

/**
 * A Nova resource for majors.
 *
 * @extends \App\Nova\Resource<\App\Models\Major>
 */
class Major extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Major::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'display_name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Demographics';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'display_name',
        'gtad_majorgroup_name',
        'whitepages_ou',
        'school',
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
    public function fields(Request $request): array
    {
        return [
            Text::make('Display Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('GTAD Group Name', 'gtad_majorgroup_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Whitepages OU')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('School')
                ->sortable()
                ->rules('required', 'max:255'),

            BelongsToMany::make('Members', 'members', User::class),

            self::metadataPanel(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Metrics\Value>
     */
    public function cards(Request $request): array
    {
        return [
            (new StudentsInMajor())->onlyOnDetail(),
            (new ActiveStudentsInMajor())->onlyOnDetail(),
            new TotalMajors(),
            new MajorsMissingDisplayNames(),
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
