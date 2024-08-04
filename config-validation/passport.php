<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('private_key')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('public_key')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('personal_access_client.id')
        ->rules([
            'required',
            'string',
            'alpha_dash',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('personal_access_client.secret')
        ->rules([
            'required',
            'string',
            'alpha_num',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('cookie_name')
        ->rules([
            'required',
            'string',
            'alpha_dash',
            'starts_with:__Host-',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),
];
