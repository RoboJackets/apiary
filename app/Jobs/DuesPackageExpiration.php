<?php declare(strict_types = 1);

namespace App\Jobs;

use App\DuesPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DuesPackageExpiration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $package;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DuesPackage $package): void
    {
        $this->package = $package;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->package->transactions as $transaction) {
            PushToJedi::dispatch($transaction->user)->onQueue('jedi');
        }
    }
}
