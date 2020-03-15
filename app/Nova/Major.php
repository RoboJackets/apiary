<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Metrics\ActiveStudentsInMajor;
use App\Nova\Metrics\MajorsMissingDisplayNames;
use App\Nova\Metrics\StudentsInMajor;
use App\Nova\Metrics\TotalMajors;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

class Major extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Major::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'display_name';

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

            new Panel(
                'Metadata',
                [
                    DateTime::make('Created', 'created_at')
                    ->onlyOnDetail(),

                    DateTime::make('Last Updated', 'updated_at')
                        ->onlyOnDetail(),
                ]
            ),
        ];
    }

    /**
     * Get the cards available for the request.
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
}
