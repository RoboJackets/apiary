<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\RemoteAttendanceLink as RAL;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

/**
 * A Nova resource for remote attendance links.
 *
 * @extends \App\Nova\Resource<\App\Models\RemoteAttendanceLink>
 */
class RemoteAttendanceLink extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = RAL::class;

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
    public static $with = [
        'attendable',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The list of recommended values for the note field.
     *
     * @var array<string>
     *
     * @phan-suppress PhanReadOnlyPublicProperty
     */
    public static $recommendedNotes = [
        'Electrical Subteam', 'Mechanical/Mechatronics Subteam', 'Software Subteam', 'Whole Team', 'Training',
        'Trainers Only', 'Discipline Core', 'Event',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        $notes = collect(self::$recommendedNotes)
            ->mapWithKeys(static fn (string $note): array => [$note => $note])->toArray();

        return [
            MorphTo::make('Team/Event', 'attendable')
                ->types([
                    Event::class,
                    Team::class,
                ])
                ->readonly(static fn (Request $request): bool => true),

            Text::make('Auto-redirecting Link', 'secret')
                ->onlyOnDetail()
                ->resolveUsing(
                    static fn (string $secret): string => route('attendance.remote.redirect', ['secret' => $secret])
                )
                ->canSee(static function (Request $request): bool {
                    if (isset($request->resourceId)) {
                        $resource = RAL::find($request->resourceId);
                        if ($resource !== null && is_a($resource, RAL::class)) {
                            return $resource->redirect_url !== null;
                        }
                    }

                    return false;
                })
                ->readonly(static fn (Request $request): bool => true)
                ->copyable(),

            Text::make('Non-redirecting Link', 'secret')
                ->onlyOnDetail()
                ->resolveUsing(static fn (string $secret): string => route('attendance.remote', ['secret' => $secret]))
                ->readonly(static fn (Request $request): bool => true)
                ->copyable(),

            Text::make('Secret')
                ->onlyOnForms()
                ->default(bin2hex(openssl_random_pseudo_bytes(32)))
                ->canSee(static fn (Request $request): bool => $request->user()->can('update-remote-attendance-links'))
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
                ->help('If you put a link here, everyone who clicks the attendance link will be redirected to that '.
                    'meeting after their attendance is recorded. If you add a redirect URL, do not share that URL '.
                    'directly. Only Google Meet, Zoom, and Microsoft Teams calls are supported currently in the '.
                    'user-facing action.')
                ->copyable(),

            // SelectOrCustom only works on forms, so use this instead on detail
            Text::make('Note')
                ->onlyOnDetail(),

            Select::make('Note')
                ->help('This can be used to keep track of what this link was used for more specifically.')
                ->required(false)
                ->onlyOnForms()
                ->options($notes),

            HasMany::make('Attendance')
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance')),

            self::metadataPanel(),
        ];
    }

    public static function searchable(): bool
    {
        return false;
    }
}
