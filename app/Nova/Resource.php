<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition.RequiredSingleLineCondition

namespace App\Nova;

use App\Exceptions\ScoutFilterConfigurationError;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource as NovaResource;
use Laravel\Scout\Builder;

/**
 * Base class for Nova resources.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @mixin TModel  @phan-suppress-current-line PhanInvalidMixin
 *
 * @method string getKey()
 */
abstract class Resource extends NovaResource
{
    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Laravel\Scout\Builder  $query
     * @return \Laravel\Scout\Builder
     */
    public static function scoutQuery(NovaRequest $request, $query): Builder
    {
        if ($request->viaResource !== null) {
            $filter_on_attribute = Str::replace('-', '_', Str::singular($request->viaResource)).'_id';

            if (
                ! in_array(
                    'filterableAttributes',
                    config('scout.meilisearch.index-settings.'.$query->model::class, []),
                    true
                )
            ) {
                throw new ScoutFilterConfigurationError(
                    'Attempted to query Scout model '.$query->model::class.' with filter '.$filter_on_attribute
                    .', but model does not have filterableAttributes configured'
                );
            }

            if (
                ! in_array(
                    $filter_on_attribute,
                    config('scout.meilisearch.index-settings.'.$query->model::class.'.filterableAttributes', []),
                    true
                )
            ) {
                if (property_exists($query->model, 'do_not_filter_on')) {
                    if (in_array($filter_on_attribute, $query->model->do_not_filter_on, true)) {
                        return $query;
                    }

                    throw new ScoutFilterConfigurationError(
                        'Attempted to query Scout model '.$query->model::class.' with filter '.$filter_on_attribute
                        .', but filter not in filterableAttributes nor $do_not_filter_on'
                    );
                }

                throw new ScoutFilterConfigurationError(
                    'Attempted to query Scout model '.$query->model::class.' with filter '.$filter_on_attribute
                    .', but filter not in filterableAttributes and model does not have $do_not_filter_on'
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
