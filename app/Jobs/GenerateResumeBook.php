<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Jobs;

use App\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateResumeBook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of attempts for this job.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The major (ou) to filter by, or null.
     *
     * @var ?string
     */
    private $major;

    /**
     * The cutoff date for resumes.
     *
     * @var string
     */
    private $resume_date_cutoff;

    /**
     * The path to the resume book output file. Only valid after handle is complete.
     *
     * @var ?string
     */
    public $path;

    /**
     * The datecode in the name of the resume book output file. Only valid after handle is complete.
     *
     * @var ?string
     */
    public $datecode;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $major, string $resume_date_cutoff)
    {
        $this->major = $major;
        $this->resume_date_cutoff = $resume_date_cutoff;
        $this->path = null;
        $this->datecode = null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = User::active()
            ->whereNotNull('resume_date')
            ->where('resume_date', '>', $this->resume_date_cutoff)
            ->get();
        $filteredUids = $users->pluck('uid');

        if (null !== $this->major) {
            $majors = $users->mapWithKeys(static function (User $user): array {
                $ous = Cache::remember('whitepages_ou_'.$user->uid, now()->addDays(1), static function () {
                    throw new Exception('This needs to be rewritten to use the local database or BuzzAPI');
                });

                return [$user->uid => $ous];
            });
            $filteredUids = $majors->filter(function (Collection $ous, string $uid): bool {
                return $ous->contains($this->major);
            })->keys();
        }

        if (0 === $filteredUids->count()) {
            $this->path = null;
            $this->datecode = null;
            throw new Exception('There are no resumes to export!');
        }

        $filenames = $filteredUids->map(static function (string $uid): string {
            return escapeshellarg(Storage::disk('local')->path('resumes/'.$uid.'.pdf'));
        });

        $this->datecode = now()->format('Y-m-d-Hi');
        $this->path = Storage::disk('local')->path('resumes/robojackets-resume-book-'.$this->datecode
            .(null !== $this->major ? '-'.$this->major : '').'.pdf');

        // Ghostscript: -q -dNOPAUSE -dBATCH for disabling interactivity, -sDEVICE= for setting output type, -dSAFER
        // because the input is untrusted
        $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dSAFER -sOutputFile='.escapeshellarg($this->path).' ';
        $cmd .= $filenames->join(' ');

        Log::debug('Running shell command: '.$cmd);
        $gsOutput = [];
        $gsExit = -1;
        exec($cmd, $gsOutput, $gsExit);

        if (0 !== $gsExit) {
            Log::error('gs did not exit cleanly (status code '.$gsExit.'), output: '.implode("\n", $gsOutput));
            $this->path = null;
            $this->datecode = null;
            throw new Exception('gs did not exit cleanly, so the resume book could not be generated.');
        }

        // This is not perfect! The original metadata is recoverable (exiftool can't remove it permanently).
        $cmdExif = 'exiftool -Title="RoboJackets Resume Book" -Creator="MyRoboJackets" -Author="RoboJackets" ';
        $cmdExif .= escapeshellarg($this->path);
        Log::debug('Running shell command: '.$cmdExif);
        $exifOutput = [];
        $exifExit = -1;
        exec($cmdExif, $exifOutput, $exifExit);

        if (0 !== $exifExit) {
            Log::error('exiftool did not exit cleanly (status code '.$exifExit.'), output: '
                .implode("\n", $exifOutput));
            $this->path = null;
            $this->datecode = null;
            throw new Exception('exif did not exit cleanly, so the resume book could not be generated.');
        }
    }
}
