<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DuesPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DuesPackageSync implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The dues package that will expire.
     *
     * @var \App\DuesPackage
     */
    private $package;

    /**
     * Create a new job instance.
     */
    public function __construct(DuesPackage $package)
    {
        $this->package = $package;
        $this->queue = 'jedi';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->package->transactions as $transaction) {
            PushToJedi::dispatch($transaction->user, DuesPackage::class, $this->package->id, 'sync');
        }
    }
}
