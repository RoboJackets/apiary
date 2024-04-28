<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [

    'dev_url' => env('APP_DEV_URL', 'https://github.com/RoboJackets/apiary'),


    'aliases' => Facade::defaultAliases()->merge([
        'Alert' => RealRashid\SweetAlert\Facades\Alert::class,
        'Cas' => Subfission\Cas\Facades\Cas::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

    'alloc_id' => env('NOMAD_ALLOC_ID'),

];
