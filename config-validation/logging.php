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
        ->environments(['test', 'sandbox', Rule::ENV_PRODUCTION]),
];
