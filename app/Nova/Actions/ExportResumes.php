<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\Major;
use App\Models\User;
use Barryvdh\DomPDF\Facade as PDF;
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
use Laravel\Nova\Fields\DateTime;

class ExportResumes extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\User>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        // Check this here because canSee was removed from App\Nova\User as this is a standalone action.
        if (! Auth::user()->can('read-users-resume')) {
            return Action::danger('Sorry! You are not authorized to perform this action.');
        }

        $majors = [];

        foreach ($fields->majors as $major => $selected) {
            if (!$selected) {
                continue;
            }

            Major::where('display_name', $major)->firstOrFail();

            $majors[] = $major;
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
            ->leftJoin(
                'majors',
                'major_user.major_id',
                '=',
                'majors.id'
            )
            ->whereIn('majors.display_name', $majors)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->pluck('uid');

        if (0 === $users->count()) {
            return Action::danger('No resumes matched the criteria!');
        }

        $filenames = $users->map(static function (string $uid): string {
            return escapeshellarg(Storage::disk('local')->path('resumes/'.$uid.'.pdf'));
        });

        $datecode = now()->format('Y-m-d-H-i-s');
        $filename = 'robojackets-resumes-'.$datecode.'.pdf';
        $path = Storage::disk('local')->path('nova-exports/'.$filename);

        $coverfilename = 'robojackets-resumes-'.$datecode.'-cover.pdf';
        $coverpath = Storage::disk('local')->path('nova-exports/'.$coverfilename);

        PDF::loadView(
            'resumecover',
            [
                'majors' => $majors,
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

        if (0 !== $gsExit) {
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

        if (0 !== $exifExit) {
            Log::error('exiftool did not exit cleanly (status code '.$exifExit.'), output: '
                .implode("\n", $exifOutput));

            return Action::danger('Error exporting resumes');
        }

        // Generate signed URL to pass to backend to facilitate file download
        $url = URL::signedRoute('api.v1.nova.export', ['file' => $filename], now()->addMinutes(5));

        return Action::download($url, $filename);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        // This is only stored for 30 seconds because it only needs to stick for one page load
        // Nova runs this function for every record in the user view so it slows down the page otherwise
        $majors = Cache::remember('majors_with_resumes', 30, static function (): array {
            return User::selectRaw('distinct(majors.display_name) as distinct_display_names')
                ->active()
                ->whereNotNull('resume_date')
                ->where('primary_affiliation', 'student')
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
                ->mapWithKeys(static function (?string $displayName): array {
                    return null === $displayName ? [] : [$displayName => $displayName];
                })
                ->toArray();
        });

        return [
            BooleanGroup::make('Majors')
                ->options($majors)
                ->help('Only include resumes for these majors')
                ->required(),

            DateTime::make('Resume Date Cutoff')
                ->help('Only include resumes uploaded after this date')
                ->required()
                ->rules('required'),
        ];
    }
}
