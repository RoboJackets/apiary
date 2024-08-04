<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('dsn')
        ->rules([
            'required',
            'string',
            'active_url',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('release')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('environment')
        ->rules([
            'nullable',
            'prohibited',
        ]),
];
