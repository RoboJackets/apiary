<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('default')
        ->rules([
            'required',
            'string',
            'in:stderr',
        ])
        ->environments(['test', 'google-play-review', Rule::ENV_PRODUCTION]),
];
