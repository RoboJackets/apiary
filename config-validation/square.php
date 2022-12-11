<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('access_token')
        ->rules([
            'required',
            'string',
            'alpha_dash',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('location_id')
        ->rules([
            'required',
            'string',
            'alpha_num',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('environment')
        ->rules([
            'required',
            'string',
            'in:sandbox',
        ])
        ->environments(['test']),

    Rule::make('environment')
        ->rules([
            'required',
            'string',
            'in:production',
        ])
        ->environments([Rule::ENV_PRODUCTION]),
];
