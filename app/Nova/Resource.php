<?php

declare(strict_types=1);

namespace App\Nova;

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

use Illuminate\Support\Str;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource as NovaResource;
use Laravel\Scout\Builder;

abstract class Resource extends NovaResource
{
    /**
     * Build a Scout search query for the given resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param \Laravel\Scout\Builder  $query
     *
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query): Builder
    {
        if (null !== $request->viaResource) {
            $filter_on_attribute = Str::replace('-', '_', Str::singular($request->viaResource)).'_id';

            if (! property_exists($query->model, 'filterable_attributes')) {
                throw new \Exception(
                    'Attempted to query Scout model '.get_class($query->model).' with filter '.$filter_on_attribute
                    .', but model does not have $filterable_attributes'
                );
            }

            if (! in_array($filter_on_attribute, $query->model->filterable_attributes, true)) {
                if (property_exists($query->model, 'do_not_filter_on')) {
                    if (in_array($filter_on_attribute, $query->model->do_not_filter_on, true)) {
                        return $query;
                    }

                    throw new \Exception(
                        'Attempted to query Scout model '.get_class($query->model).' with filter '.$filter_on_attribute
                        .', but filter not in $filterable_attributes nor $do_not_filter_on'
                    );
                }

                throw new \Exception(
                    'Attempted to query Scout model '.get_class($query->model).' with filter '.$filter_on_attribute
                    .', but filter not in $filterable_attributes and model does not have $do_not_filter_on'
                );
            }

            return $query->where($filter_on_attribute, $request->viaResourceId);
        }

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
