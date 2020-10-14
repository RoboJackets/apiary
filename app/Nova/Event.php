<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\RsvpSourceBreakdown;
use App\Nova\ResourceTools\CollectAttendance;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for events.
 *
 * @property int $id The database ID for this event
 */
class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Event::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Meetings';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            Text::make('Event Name', 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            (new BelongsTo('Organizer', 'organizer', User::class))
                ->searchable()
                ->rules('required')
                ->help('The organizer of the event'),

            DateTime::make('Start Time')
                ->hideFromIndex(),

            DateTime::make('End Time')
                ->hideFromIndex(),

            Text::make('Location')
                ->hideFromIndex()
                ->rules('max:255'),

            Currency::make('Cost')
                ->hideFromIndex()
                ->rules('required'),

            Boolean::make('Anonymous RSVP', 'allow_anonymous_rsvp')
                ->hideFromIndex(),

            Text::make('RSVP URL', function (): string {
                return route('events.rsvp', ['event' => $this->id]);
            })->onlyOnDetail(),

            HasMany::make('RSVPs')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-rsvps');
                }),

            MorphMany::make('Remote Attendance Links')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-remote-attendance-links');
                }),

            MorphMany::make('Attendance')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),

            CollectAttendance::make()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                }),

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
        return [
            (new RsvpSourceBreakdown())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-rsvps');
                }),
            (new ActiveAttendanceBreakdown(true))
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),
        ];
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
        return [
            (new Actions\CreateRemoteAttendanceLink())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                })
                ->canRun(static function (Request $request): bool {
                    return $request->user()->can('create-attendance');
                })
                ->confirmText('Are you sure you want to create a remote attendance link?')
                ->confirmButtonText('Create Link')
                ->cancelButtonText('Cancel'),
        ];
    }
}
