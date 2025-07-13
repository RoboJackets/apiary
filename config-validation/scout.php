<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('driver')
        ->rules([
            'required',
            'string',
            'in:collection',
        ]),

    Rule::make('driver')
        ->rules([
            'required',
            'string',
            'in:meilisearch',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('prefix')
        ->rules([
            'required',
            'string',
            'alpha_dash',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('queue.queue')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('identify')
        ->rules([
            'required',
            'boolean',
            'declined',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('meilisearch.host')
        ->rules([
            'required',
            'string',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('meilisearch.key')
        ->rules([
            'required',
            'string',
            'uuid',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),
];
