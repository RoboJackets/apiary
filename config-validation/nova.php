<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('license_key')
        ->rules([
            'required',
            'string',
            'alpha_num',
        ])
        ->environments([Rule::ENV_PRODUCTION]),

    Rule::make('domain')
        ->rules([
            'nullable',
            'prohibited',
        ]),
];
