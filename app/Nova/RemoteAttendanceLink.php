<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Nova;

use App\RemoteAttendanceLink as RAL;
use Carbon\Carbon;
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
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

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
     *
     * @suppress PhanTypeInvalidCallableArraySize
     */
    public function fields(Request $request): array
    {
        return [
            MorphTo::make('Team/Event', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ])
                ->readonly(static function (Request $request): bool {
                    return true;
                }),

            Text::make('Auto-redirecting Link', 'secret')
                ->onlyOnDetail()
                ->resolveUsing(static function (string $secret): string {
                    return route('attendance.remote.redirect', ['secret' => $secret]);
                })
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = RAL::find($request->resourceId);
                        if (null !== $resource && is_a($resource, RAL::class)) {
                            return null !== $resource->redirect_url;
                        }
                    }

                    return false;
                })
                ->readonly(static function (Request $request): bool {
                    return true;
                }),

            Text::make('Non-redirecting Link', 'secret')
                ->onlyOnDetail()
                ->resolveUsing(static function (string $secret): string {
                    return route('attendance.remote', ['secret' => $secret]);
                })
                ->readonly(static function (Request $request): bool {
                    return true;
                }),

            Text::make('Secret')
                ->onlyOnForms()
                ->default(hash('sha256', random_bytes(64)))
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('update-remote-attendance-links');
                })
                ->creationRules('unique:remote_attendance_links,secret')
                ->updateRules('unique:remote_attendance_links,secret,{{resourceId}}')
                ->help('This is contained in the attendance URL that will be shared. The default value for this field'.
                    ' is randomly generated.'),

            DateTime::make('Expires At')
                ->default(Carbon::now()->addHours(4))
                ->help('This defaults to four hours in the future.'),

            Text::make('Redirect URL')
                ->sortable()
                ->required(false)
                ->rules('nullable', 'url')
                ->help('If you put a link to a BlueJeans or Google Meet meeting here, everyone who clicks the '.
                    'attendance link will be redirected to that meeting after their attendance is recorded. If '.
                    'you add a redirect URL, do not share the meeting link directly. Only Google Meet and '.
                    'BlueJeans calls are supported currently in the user-facing action.'),

            Text::make('Note')
                ->hideFromIndex()
                ->required(false)
                ->help('This can be used to keep track of what this link was used for more specifically. Press the '.
                    'down arrow for suggestions.')
                ->suggestions(['Electrical', 'Mechanical', 'Software', 'Firmware', 'Mechatronics', 'Whole Team']),

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
