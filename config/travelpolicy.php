<?php

declare(strict_types=1);

return [
    /**
     * The lowest trip fee that is permitted on a trip, in dollars.
     */
    'minimum_trip_fee' => intval(env('TRAVEL_POLICY_MINIMUM_TRIP_FEE', 20)),

    /**
     * The lowest trip-fee-to-cost ratio that is permitted on a trip, as a decimal.
     *
     * For example, a trip fee of $20 for a trip with a total per-person cost of $100 has a fee-to-cost ratio of 0.2.
     */
    'minimum_trip_fee_cost_ratio' => floatval(env('TRAVEL_POLICY_MINIMUM_FEE_TO_COST_RATIO', 20)),
];
