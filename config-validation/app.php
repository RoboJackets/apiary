<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('url')->rules([
        'required',
        'string',
        'active_url',
    ]),
];
