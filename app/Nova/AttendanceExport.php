<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Fields\Hidden;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;

class AttendanceExport extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\AttendanceExport::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['downloadUser'];

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
    public static $group = 'Other';

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            DateTime::make('Start Time')
                ->hideFromIndex()
                ->rules('required'),

            DateTime::make('End Time')
                ->hideFromIndex()
                ->rules('required'),

            Hidden::make('Link', 'secret')
                ->onlyOnDetail()
                ->resolveUsing(static function (?string $secret): ?string {
                    return null === $secret ? null : route('attendance.export', ['secret' => $secret]);
                })
                ->readonly(static function (Request $request): bool {
                    return true;
                }),

            Text::make('Secret')
                ->onlyOnForms()
                ->readonly(static function (Request $request): bool {
                    return ! $request->user()->hasRole('admin');
                })
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })
                ->rules('required')
                ->creationRules('unique:attendance_exports,secret')
                ->updateRules('unique:attendance_exports,secret,{{resourceId}}'),

            DateTime::make('Expires At')
                ->rules('required'),

            BelongsTo::make('Downloaded By', 'downloadUser', User::class)
                ->hideWhenCreating()
                ->nullable(),

            DateTime::make('Downloaded At')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->nullable(),

            self::metadataPanel(),
        ];
    }
}
