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
 *
 * @extends \Laravel\Nova\Resource<\Illuminate\Database\Eloquent\Model>
 */
abstract class Resource extends NovaResource
{
    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Scout\Builder  $query
     */
    #[\Override]
    public static function scoutQuery(NovaRequest $request, $query): Builder
    {
        if ($request->viaResource !== null) {
            $filter_on_attribute = Str::replace('-', '_', Str::singular($request->viaResource)).'_id';
            $class = $query->model::class;

            if (! array_key_exists('filterableAttributes', config('scout.meilisearch.index-settings.'.$class, []))) {
                if (defined($query->model::class.'::DO_NOT_FILTER_ON')) {
                    if (in_array($filter_on_attribute, $query->model::DO_NOT_FILTER_ON, true)) {
                        return $query;
                    }

                    throw new ScoutFilterConfigurationError(
                        'Attempted to query Scout model '.$class.' with filter '.$filter_on_attribute
                        .', but model does not have filterableAttributes configured and filter not in DO_NOT_FILTER_ON'
                    );
                } else {
                    throw new ScoutFilterConfigurationError(
                        'Attempted to query Scout model '.$class.' with filter '.$filter_on_attribute
                        .', but model does not have filterableAttributes configured and model does not have '
                        .'DO_NOT_FILTER_ON'
                    );
                }
            }

            if (
                ! in_array(
                    $filter_on_attribute,
                    config('scout.meilisearch.index-settings.'.$class.'.filterableAttributes', []),
                    true
                )
            ) {
                if (defined($query->model::class.'::DO_NOT_FILTER_ON')) {
                    if (in_array($filter_on_attribute, $query->model::DO_NOT_FILTER_ON, true)) {
                        return $query;
                    }

                    throw new ScoutFilterConfigurationError(
                        'Attempted to query Scout model '.$class.' with filter '.$filter_on_attribute
                        .', but filter not in filterableAttributes nor DO_NOT_FILTER_ON'
                    );
                }

                throw new ScoutFilterConfigurationError(
                    'Attempted to query Scout model '.$class.' with filter '.$filter_on_attribute
                    .', but filter not in filterableAttributes and model does not have DO_NOT_FILTER_ON'
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
