<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource as NovaResource;

abstract class Resource extends NovaResource
{
    /**
     * Build an "index" query for the given resource.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query;
    }

    /**
     * Timestamp fields.
     */
    protected static function metadataPanel(): Panel
    {
        return new Panel(
            'Metadata',
            [
                DateTime::make('Created', 'created_at')
                    ->onlyOnDetail(),

                DateTime::make('Last Updated', 'updated_at')
                    ->onlyOnDetail(),
            ]
        );
    }
}
