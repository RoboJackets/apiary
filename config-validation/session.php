<?php

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('driver')
        ->rules([
            'required',
            'string',
            'in:redis',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('cookie')
        ->rules([
            'required',
            'string',
            'alpha_dash',
            'starts_with:__Host-'
        ])
        ->environments(['test', 'google-play-review', Rule::ENV_PRODUCTION]),

    Rule::make('domain')
        ->rules([
            'nullable',
            'prohibited',
        ]),

    Rule::make('secure')
        ->rules([
            'required',
            'boolean',
            'accepted',
        ])
        ->environments(['test', 'google-play-review', Rule::ENV_PRODUCTION]),

    Rule::make('http_only')
        ->rules([
            'required',
            'boolean',
            'accepted',
        ])
        ->environments(['test', 'google-play-review', Rule::ENV_PRODUCTION]),

    Rule::make('same_site')
        ->rules([
            'required',
            'string',
            'in:lax',
        ]),
];
