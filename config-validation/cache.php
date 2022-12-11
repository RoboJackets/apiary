<?php

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('default')
        ->rules([
            'required',
            'string',
            'in:redis'
        ])
        ->environments(['test', Rule::ENV_PRODUCTION]),
];
