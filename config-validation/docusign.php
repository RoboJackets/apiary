<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('ingest_mailbox')
        ->rules([
            'required',
            'string',
            'email:rfc,strict,dns,spoof',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),

    Rule::make('single_sign_on_url')
        ->rules([
            'required',
            'string',
            'active_url',
        ]),
];
