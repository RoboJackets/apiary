<?php

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('url')->rules([
        'required',
        'string',
        'active_url',
    ]),
];
