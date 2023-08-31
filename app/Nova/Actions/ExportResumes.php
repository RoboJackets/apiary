<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\ClassStanding;
use App\Models\Major;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportResumes extends Action
{
    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Indicates if the action can be run without any models.
     *
     * @var bool
     */
    public $standalone = true;

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Export';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Note that any filters selected outside of this popup are ignored.';

    /**
     * Disables action log events for this action.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\User>  $models
     *
     * @phan-suppress PhanDeprecatedFunction
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (! Auth::user()->can('read-users-resume')) {
            return Action::danger('Sorry! You are not authorized to perform this action.');
        }

        $majors = [];

        foreach ($fields->majors as $major => $selected) {
            if ($selected === false) {
                continue;
            }

            Major::where('display_name', $major)->firstOrFail();

            $majors[] = $major;
        }

        $classStandings = [];

        foreach ($fields->class_standings as $classStanding => $selected) {
            if ($selected === false) {
                continue;
            }

            ClassStanding::where('name', $classStanding)->sole();

            $classStandings[] = $classStanding;
        }

        $users = User::active()
            ->whereNotNull('resume_date')
            ->where('resume_date', '>', $fields->resume_date_cutoff)
            ->where('primary_affiliation', 'student')
            ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                $q->where('restricted_to_students', false);
            })
            ->leftJoin('major_user', static function (JoinClause $join): void {
                $join->on('users.id', '=', 'major_user.user_id')
                    ->whereNull('major_user.deleted_at');
            })
            ->leftJoin('majors', 'major_user.major_id', '=', 'majors.id')
            ->leftJoin('class_standing_user', static function (JoinClause $join): void {
                $join->on('users.id', '=', 'class_standing_user.user_id')
                    ->whereNull('class_standing_user.deleted_at');
            })
            ->leftJoin('class_standings', 'class_standing_user.class_standing_id', '=', 'class_standings.id')
            ->whereIn('majors.display_name', $majors)
            ->whereIn('class_standings.name', $classStandings)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->pluck('uid');

        if ($users->count() === 0) {
            return Action::danger('No resumes matched the provided criteria!');
        }

        $filenames = $users->uniqueStrict()->map(
            static fn (string $uid): string => escapeshellarg(Storage::disk('local')->path('resumes/'.$uid.'.pdf'))
        );

        $datecode = now()->format('Y-m-d-H-i-s');
        $filename = 'robojackets-resumes-'.$datecode.'.pdf';
        $path = Storage::disk('local')->path('nova-exports/'.$filename);

        $coverfilename = 'robojackets-resumes-'.$datecode.'-cover.pdf';
        $coverpath = Storage::disk('local')->path('nova-exports/'.$coverfilename);

        Pdf::loadView(
            'resumecover',
            [
                'majors' => $majors,
                'class_standings' => $classStandings,
                'cutoff_date' => $fields->resume_date_cutoff,
                'generation_date' => $datecode,
            ]
        )->save($coverpath);

        // Ghostscript: -q -dNOPAUSE -dBATCH for disabling interactivity, -sDEVICE= for setting output type, -dSAFER
        // because the input is untrusted
        $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dSAFER -sOutputFile='.escapeshellarg($path).' ';
        $cmd .= $coverpath.' ';
        $cmd .= $filenames->join(' ');

        Log::debug('Running shell command: '.$cmd);
        $gsOutput = [];
        $gsExit = -1;
        exec($cmd, $gsOutput, $gsExit);

        if ($gsExit !== 0) {
            Log::error('gs did not exit cleanly (status code '.$gsExit.'), output: '.implode("\n", $gsOutput));

            return Action::danger('Error exporting resumes');
        }

        // This is not perfect! The original metadata is recoverable (exiftool can't remove it permanently).
        $cmdExif = 'exiftool -Title="RoboJackets Resumes" -Creator="MyRoboJackets" -Author="RoboJackets" ';
        $cmdExif .= escapeshellarg($path);
        Log::debug('Running shell command: '.$cmdExif);
        $exifOutput = [];
        $exifExit = -1;
        exec($cmdExif, $exifOutput, $exifExit);

        if ($exifExit !== 0) {
            Log::error('exiftool did not exit cleanly (status code '.$exifExit.'), output: '
                .implode("\n", $exifOutput));

            return Action::danger('Error exporting resumes');
        }

        // Generate signed URL to pass to frontend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return Action::download($url, $filename)
            ->withMessage('The resumes were successfully exported!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        // This is only stored for 30 seconds because it only needs to stick for one page load
        // Nova runs this function for every record in the user view so it slows down the page otherwise
        $majors = Cache::remember(
            'majors_with_resumes',
            30,
            static fn (): array => User::selectRaw('distinct(majors.display_name) as distinct_display_names')
                ->active()
                ->whereNotNull('resume_date')
                ->where('primary_affiliation', 'student')
                ->where('is_service_account', '=', false)
                ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                    $q->where('restricted_to_students', false);
                })
                ->leftJoin('major_user', static function (JoinClause $join): void {
                    $join->on('users.id', '=', 'major_user.user_id')
                        ->whereNull('major_user.deleted_at');
                })
                ->leftJoin(
                    'majors',
                    'major_user.major_id',
                    '=',
                    'majors.id'
                )
                ->orderBy('distinct_display_names')
                ->pluck('distinct_display_names')
                ->mapWithKeys(
                    static fn (?string $name): array => $name === null ? [] : [$name => $name]
                )
                ->toArray()
        );

        $classStandings = Cache::remember('class_standings_with_resumes', 30, static fn (): array => User::selectRaw(
            'distinct(class_standings.name) as distinct_class_standings, class_standings.rank_order'
        )
            ->active()
            ->whereNotNull('resume_date')
            ->where('primary_affiliation', 'student')
            ->where('is_service_account', '=', false)
            ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                $q->where('restricted_to_students', false);
            })
            ->leftJoin('class_standing_user', static function (JoinClause $join): void {
                $join->on('users.id', '=', 'class_standing_user.user_id')
                    ->whereNull('class_standing_user.deleted_at');
            })
            ->leftJoin(
                'class_standings',
                'class_standing_user.class_standing_id',
                '=',
                'class_standings.id'
            )
            ->orderBy('class_standings.rank_order')
            ->pluck('distinct_class_standings')
            ->mapWithKeys(static fn (?string $name): array => $name === null ? [] : [$name => ucfirst($name)])
            ->toArray());

        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;

        if ($month <= 8) {
            $year--;
        }

        $defaultDate = Carbon::create($year, 8, 1, 0, 0, 0, config('app.timezone'));

        return [
            BooleanGroup::make('Majors')
                ->options($majors)
                ->help('Only include resumes for these majors.')
                ->required(),

            BooleanGroup::make('Class Standings')
                ->options($classStandings)
                ->help('Only include resumes for these class standings.')
                ->required(),

            // "before:yesterday" stops users from putting in a date that doesn't make sense that will generate an
            // empty output. "yesterday" is either 7PM or 8PM yesterday, depending on timezones (EST vs EDT).
            Date::make('Resume Date Cutoff')
                ->help('Only include resumes uploaded after this date. This should generally be the start date of the'.
                    ' fall semester. When users upload a resume, all older resumes are deleted.')
                ->default($defaultDate)
                ->required()
                ->rules('required', 'before:yesterday'),
        ];
    }
}
