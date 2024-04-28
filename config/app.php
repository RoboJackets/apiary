<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [

    'dev_url' => env('APP_DEV_URL', 'https://github.com/RoboJackets/apiary'),

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Laravel Framework Service Providers...
         */

        /*
         * Package Service Providers...
         */
        Subfission\Cas\CasServiceProvider::class,
        RealRashid\SweetAlert\SweetAlertServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\NovaServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Alert' => RealRashid\SweetAlert\Facades\Alert::class,
        'Cas' => Subfission\Cas\Facades\Cas::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

    'alloc_id' => env('NOMAD_ALLOC_ID'),

];
