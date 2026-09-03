<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Resume as AppModelsResume;
use App\Nova\Actions\ExportFilteredResumes;
use App\Nova\Actions\ExportFullYearResumes;
use Illuminate\Http\Request;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Textarea;
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
    public static $title = 'id';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Other';

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['user'];

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
            DateTime::make('Last Uploaded', 'updated_at')
                ->rules('required')
                ->sortable(),
            Boolean::make('Visible to Sponsors', 'is_visible')
                ->rules('required')
                ->sortable(),
            File::make('Resume', 'storage_path')
                ->path('resumes')
                ->disk('local')
                ->deletable(false)
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    $r = AppModelsResume::find($request->resourceId);
                    if ($r && $r->user->is($request->user())) {
                        return true;
                    }

                    return $request->user()->can('read-users-resume');
                }),
            Textarea::make('Extracted Text', static function (AppModelsResume $resume) {
                $raw = AppModelsResume::search('')
                    ->where('id', $resume->id)
                    ->raw();

                return $raw['hits'][0]['extracted_text'] ?? '';
            })
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    $r = AppModelsResume::find($request->resourceId);
                    if ($r && $r->user->is($request->user())) {
                        return true;
                    }

                    return $request->user()->can('read-users-resume');
                }),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    #[\Override]
    public function actions(NovaRequest $request): array
    {
        return $request->user()->can('read-users-resume') ?
            [
                ExportFilteredResumes::make()
                    ->canSee(static fn (Request $r): bool => $r->user()->can('read-users-resume')),
                ExportFullYearResumes::make()
                    ->canSee(static fn (Request $r): bool => $r->user()->can('read-users-resume')),
            ] : [
                Action::danger(
                    ExportFilteredResumes::make()->name(),
                    'You do not have access to export resumes.'
                )
                    ->withoutConfirmation()
                    ->withoutActionEvents()
                    ->standalone()
                    ->onlyOnIndex()
                    ->canRun(static fn (): bool => true),
                Action::danger(
                    ExportFullYearResumes::make()->name(),
                    'You do not have access to export resumes.'
                )
                    ->withoutConfirmation()
                    ->withoutActionEvents()
                    ->standalone()
                    ->onlyOnIndex()
                    ->canRun(static fn (): bool => true),
            ];
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Resume>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Resume>
     */
    #[\Override]
    public static function indexQuery(NovaRequest $request, $query)
    {
        if ($request->get('orderBy') === 'is_visible') {
            $direction = $request->get('orderByDirection', 'asc');

            $query->addSelect([
                'is_visible_sort' => User::selectRaw('1')
                    ->whereColumn('users.id', 'resumes.user_id')
                    ->active()
                    ->where('primary_affiliation', 'student')
                    ->where('is_service_account', false)
                    ->whereDoesntHave('duesPackages', static function ($q) {
                        $q->where('restricted_to_students', false);
                    })
                    ->limit(1),
            ])
                ->orderByRaw('is_visible_sort IS NULL')
                ->orderBy('is_visible_sort', $direction);

            return $query;
        }

        return $query;
    }
}
