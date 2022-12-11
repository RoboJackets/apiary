<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('private_key')
        ->rules([
            'required',
            'string',
        ]),

    Rule::make('public_key')
        ->rules([
            'required',
            'string',
        ]),

    Rule::make('personal_access_client.id')
        ->rules([
            'required',
            'string',
            'alpha_dash',
        ]),

    Rule::make('personal_access_client.secret')
        ->rules([
            'required',
            'string',
            'alpha_num',
        ]),

    Rule::make('cookie_name')
        ->rules([
            'required',
            'string',
            'alpha_dash',
            'starts_with:__Host-',
        ])
        ->environments(['test', 'google-play-review', Rule::ENV_PRODUCTION]),
];
