<?php

declare(strict_types=1);

namespace App\Observers;

use App\DuesPackage;
use App\Jobs\DuesPackageSync;

class DuesPackageObserver
{
    public function saved(DuesPackage $package): void
    {
        DuesPackageSync::dispatch($package);
    }
}
