<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\DuesPackageSync;
use App\Models\DuesPackage;

class DuesPackageObserver
{
    public function saved(DuesPackage $package): void
    {
        DuesPackageSync::dispatch($package);

        if (null !== $package->access_start && $package->access_start > date('Y-m-d H:i:s')) {
            DuesPackageSync::dispatch($package)->delay($package->access_start);
        }
        if (null === $package->access_end || $package->access_end <= date('Y-m-d H:i:s')) {
            return;
        }

        DuesPackageSync::dispatch($package)->delay($package->access_end);
    }
}
