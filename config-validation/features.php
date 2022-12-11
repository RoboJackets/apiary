<?php

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('resumes')
        ->rules([
            'required',
            'boolean',
        ]),

    Rule::make('card-present-payments')
        ->rules([
            'required',
            'boolean',
        ]),

    Rule::make('docusign-membership-agreement')
        ->rules([
            'required',
            'boolean',
        ]),

    Rule::make('demo-mode')
        ->rules([
            'nullable',
            'string',
        ]),

    Rule::make('demo-mode')
        ->rules([
            'nullable',
            'prohibited',
        ])
        ->environments([Rule::ENV_PRODUCTION]),
];
