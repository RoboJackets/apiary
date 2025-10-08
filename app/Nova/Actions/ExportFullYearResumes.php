<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Models\FiscalYear;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use ZipArchive;

class ExportFullYearResumes extends Action
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
    public $confirmText = 'This will generate a resume book with all members for the selected fiscal year.';

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
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (! Auth::user()->can('read-users-resume')) {
            return Action::danger('Sorry! You are not authorized to perform this action.');
        }

        $fiscal_year_id = FiscalYear::where('ending_year', '=', $fields->fiscal_year)->sole()->id;

        $users = User::whereHas('dues', static function (Builder $q) use ($fiscal_year_id): void {
            $q->whereHas('for', static function (Builder $q) use ($fiscal_year_id): void {
                $q->where('fiscal_year_id', '=', $fiscal_year_id);
            })
                ->paid();
        })
            ->whereNotNull('resume_date')
            ->whereDoesntHave('duesPackages', static function (Builder $q): void {
                $q->where('restricted_to_students', false);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->pluck('uid');

        if ($users->count() === 0) {
            return Action::danger('No resumes matched the provided criteria!');
        }

        $filenames = $users->uniqueStrict()->map(
            static fn (string $uid): string => Storage::disk('local')->path('resumes/'.$uid.'.pdf')
        );

        return $fields->output_type === 'mono' ?
            $this->exportMono($filenames) :
            $this->exportZip($filenames);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Fiscal Year')
                ->options(
                    FiscalYear::all()
                        ->mapWithKeys(static fn (FiscalYear $year): array => [$year->ending_year => $year->ending_year])
                        ->toArray()
                ),
            Select::make('Output Type')
                ->options([
                    'mono' => 'Single PDF',
                    'zip' => 'Zip Archive of PDFs',
                ])
                ->displayUsingLabels()
                ->rules('required'),
        ];
    }

    private function exportZip(Collection $filenames)
    {
        $datecode = now()->format('Y-m-d-H-i-s');
        $outfilename = 'robojackets-resumes-'.$datecode.'.zip';
        $path = Storage::disk('local')->path('nova-exports/'.$outfilename);
        $outdir = Storage::disk('local')->path('nova-exports/robojackets-resumes-'.$datecode);

        $coverfilename = 'robojackets-resumes-'.$datecode.'-cover.pdf';
        $coverpath = Storage::disk('local')->path('nova-exports/'.$coverfilename);

        if (! is_dir($outdir)) {
            mkdir($outdir, 0755, true);
        }

        $dir = dirname($coverpath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Pdf::loadView(
            'resumecover',
            [
                'majors' => [],
                'class_standings' => [],
                'cutoff_date' => '',
                'generation_date' => $datecode,
            ]
        )->save($coverpath);

        $filenames_dirty = array_merge($filenames->toArray(), [$coverpath]);
        $filenames_cleaned = [];
        foreach ($filenames_dirty as $f) {
            $f_trimmed = $outdir.'/'.basename($f);
            $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dSAFER -sOutputFile=';
            $cmd .= escapeshellarg($f_trimmed).' ';
            $cmd .= escapeshellarg($f);
            $gsOutput = [];
            $gsExit = -1;
            exec($cmd, $gsOutput, $gsExit);

            if ($gsExit !== 0) {
                Log::error('gs did not exit cleanly (status code '.$gsExit.'), output: '.implode("\n", $gsOutput));

                return Action::danger('Error sanitizing PDFs.');
            }

            array_push($filenames_cleaned, $f_trimmed);
        }

        $archive = new ZipArchive();

        if ($archive->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($filenames_cleaned as $f) {
                if (! file_exists($f)) {
                    return Action::danger('GhostScript did not create the file '.$f);
                }
                $archive->addFile($f, basename($f));
            }
            $archive->close();
        } else {
            return Action::danger('Error exporting resumes to ZIP.');
        }

        $url = URL::signedRoute('api.v1.nova.export', ['file' => $outfilename], now()->addMinutes(5));

        return ActionResponse::download($outfilename, $url)
            ->withMessage('The resumes were successfully exported!');
    }

    private function exportMono(Collection $filenames)
    {
        $filenames = $filenames->map(static fn ($f) => escapeshellarg($f));

        $datecode = now()->format('Y-m-d-H-i-s');
        $filename = 'robojackets-resumes-'.$datecode.'.pdf';
        $path = Storage::disk('local')->path('nova-exports/'.$filename);

        $coverfilename = 'robojackets-resumes-'.$datecode.'-cover.pdf';
        $coverpath = Storage::disk('local')->path('nova-exports/'.$coverfilename);

        $dir = dirname($coverpath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Pdf::loadView(
            'resumecover',
            [
                'majors' => [],
                'class_standings' => [],
                'cutoff_date' => '',
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

        return ActionResponse::download($filename, $url)
            ->withMessage('The resumes were successfully exported!');
    }
}
