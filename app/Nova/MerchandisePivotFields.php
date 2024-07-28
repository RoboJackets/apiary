<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;

class MerchandisePivotFields
{
    /**
     * Get the pivot fields for the relationship.
     */
    public function __invoke(): array
    {
        return [
            DateTime::make('Provided At')
                ->onlyOnIndex()
                ->sortable(),

            BelongsTo::make('Provided By', 'providedBy', User::class)
                ->onlyOnIndex(),

            Text::make('Provided Via')
                ->onlyOnIndex()
                ->sortable(),
        ];
    }
}
