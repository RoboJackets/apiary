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

    Rule::make('base_url')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),
];
