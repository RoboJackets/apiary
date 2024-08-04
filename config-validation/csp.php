<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('policy')
        ->rules([
            'required',
            'string',
        ]),

    Rule::make('report_uri')
        ->rules([
            'required',
            'string',
            'active_url',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

    Rule::make('enabled')
        ->rules([
            'required',
            'boolean',
            'accepted',
        ])
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),

];
