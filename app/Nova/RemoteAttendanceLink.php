<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for remote attendance links.
 */
class RemoteAttendanceLink extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\RemoteAttendanceLink::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Meetings';

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['attendable'];

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
            MorphTo::make('Team/Event', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ]),

            Text::make('Link', 'secret')
                ->onlyOnDetail()
                ->resolveUsing(static function (string $secret): string {
                    return route('attendance.remote.redirect', ['secret' => $secret]);
                })
                ->readonly(static function (Request $request): bool {
                    return true;
                })
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                }),

            Text::make('Secret')
                ->onlyOnForms()
                ->readonly(static function (Request $request): bool {
                    return ! $request->user()->hasRole('admin');
                })
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })
                ->creationRules('unique:teams,secret')
                ->updateRules('unique:teams,secret,{{resourceId}}'),

            DateTime::make('Expires At')
                ->hideFromIndex()
                ->readonly(static function (Request $request): bool {
                    return ! $request->user()->hasRole('admin');
                })
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                }),

            Text::make('Redirect URL')
                ->hideFromIndex()
                ->sortable(),

            Text::make('Note')
                ->hideFromIndex()
                ->help('This can be used to keep track of what this link was used for more specifically. Press the '.
                    'down arrow for suggestions.')
                ->suggestions(['Electrical', 'Mechanical', 'Software', 'Firmware', 'Mechatronics', 'Whole Team'])

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    /**
     * Timestamp fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function metaFields(): array
    {
        return [
            DateTime::make('Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
