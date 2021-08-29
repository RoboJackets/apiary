<?php

namespace App\Nova;

use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;

class MerchandisePivotFields
{
    /**
     * Get the pivot fields for the relationship.
     */
    public function __invoke(): array
    {
        return [
            DateTime::make('Provided At')
                ->onlyOnIndex(),

            BelongsTo::make('Provided By', 'providedBy', User::class)
                ->onlyOnIndex(),
        ];
    }
}
