<?php

namespace App\Observers;

use App\DuesPackage;
use App\Jobs\DuesPackageExpiration;

class DuesPackageObserver
{
    public function saved(DuesPackage $package)
    {
        DuesPackageExpiration::dispatch($package)->onQueue('jedi');

        if (null !== $package->access_start && $package->access_start > date('Y-m-d H:i:s')) {
            DuesPackageExpiration::dispatch($package)->delay($package->access_start)->onQueue('jedi');
        }
        if (null !== $package->access_end && $package->access_end > date('Y-m-d H:i:s')) {
            DuesPackageExpiration::dispatch($package)->delay($package->access_end)->onQueue('jedi');
        }
    }
}
