<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('resumes')
        ->rules([
            'required',
            'boolean',
        ]),

    Rule::make('card-present-payments')
        ->rules([
            'required',
            'boolean',
        ]),

    Rule::make('sandbox-mode')
        ->rules([
            'required',
            'boolean',
        ]),

    Rule::make('sandbox-mode')
        ->rules([
            'required',
            'boolean',
            'declined',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('sandbox-users')
        ->rules([
            'array',
        ]),

    Rule::make('sandbox-users')
        ->rules([
            'array',
            'prohibited',
        ])
        ->environments([Rule::ENV_PRODUCTION]),
];
