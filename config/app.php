<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Facade;

return [
    'timezone' => 'America/New_York',

    'aliases' => Facade::defaultAliases()->merge([
        'Alert' => RealRashid\SweetAlert\Facades\Alert::class,
        'Cas' => Subfission\Cas\Facades\Cas::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

    'alloc_id' => env('NOMAD_ALLOC_ID'),
];
