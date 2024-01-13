<?php

declare(strict_types=1);

use AshAllenDesign\ConfigValidator\Services\Rule;

return [
    Rule::make('minimum_trip_fee')
        ->rules([
            'required',
            'integer',
            'min:1',
        ]),

    Rule::make('minimum_trip_fee_cost_ratio')
        ->rules([
            'required',
            'decimal:1,2',
        ]),
];
