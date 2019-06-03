<?php declare(strict_types = 1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Metrics\RsvpSourceBreakdown;
use App\Nova\Metrics\ActiveAttendanceBreakdown;

class Event extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Event';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Fields\Field>
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

            new Panel('Metadata', $this->metaFields()),

            HasMany::make('RSVPs')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-rsvps');
                }),

            HasMany::make('Attendance')
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),
        ];
    }

    /**
     * Timestamp fields
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
     * @param \Illuminate\Http\Request  $request
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
     * @param \Illuminate\Http\Request  $request
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
     * @param \Illuminate\Http\Request  $request
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
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [];
    }
}
