<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('default')
        ->rules([
            'required',
            'string',
            'in:postmark',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('default')
        ->rules([
            'required',
            'string',
            'in:log',
        ])
        ->environments(['sandbox']),
];
