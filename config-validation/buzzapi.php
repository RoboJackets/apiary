<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('host')
        ->rules([
            'required',
            'string',
        ]),

    Rule::make('host')
        ->rules([
            'required',
            'string',
            'in:api.gatech.edu',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('app_id')
        ->rules([
            'required',
            'string',
            'alpha_dash',
        ]),

    Rule::make('app_password')
        ->rules([
            'required',
            'string',
        ]),

    Rule::make('default_log_level')
        ->rules([
            'required',
            'string',
            'in:info,warn,debug',
        ]),

    Rule::make('timeout')
        ->rules([
            'required',
            'integer',
            'numeric',
        ]),

    Rule::make('connect_timeout')
        ->rules([
            'required',
            'integer',
            'numeric',
        ]),
];
