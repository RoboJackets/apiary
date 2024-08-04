<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('redis.client')
        ->rules([
            'required',
            'string',
            'in:phpredis',
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),
];
