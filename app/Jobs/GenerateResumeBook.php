<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Adldap\Laravel\Facades\Adldap;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateResumeBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $this->tries = 1;
    }

    /**
     * Execute the job.
     *
     * @return void
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
                $search = Adldap::search()->where('uid', '=', $user->uid)->select('uid', 'ou')->first();
                $uid = $search['uid'][0];
                $ou = $search['ou'][0];

                return [$uid => $ou];
            });
            $filteredUids = $majors->filter(function (string $ou, string $uid): bool {
                return $ou === $this->major;
            })->keys();
        }

        if (0 === $filteredUids->count()) {
            $this->path = null;
            throw new \Exception('There are no resumes to export!');
        }

        $filenames = $filteredUids->map(static function ($uid) {
            return escapeshellarg(Storage::disk('local')->path('resumes/'.$uid.'.pdf'));
        });

        $this->datecode = now()->format('Y-m-d-Hi');
        $this->path = Storage::disk('local')->path('resumes/resume-book-'.$this->datecode.'.pdf');
        // Ghostscript: -q -dNOPAUSE -dBATCH for disabling interactivity, -sDEVICE= for setting output type, -dSAFER
        // because the input is untrusted
        $cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dSAFER -sOutputFile='.escapeshellarg($this->path).' ';
        $cmd .= $filenames->join(' ');

        \Log::debug('Running shell command: '.$cmd);
        $gsOutput = [];
        $gsExit = -1;
        exec($cmd, $gsOutput, $gsExit);

        if (0 !== $gsExit) {
            \Log::error('gs did not exit cleanly (status code '.$gsExit.'), output: '.implode("\n", $gsOutput));
            $this->path = null;
            throw new \Exception('gs did not exit cleanly, so the resume book could not be generated.');
        }
    }
}
