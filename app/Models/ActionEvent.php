<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

namespace App\Models;

use Laravel\Nova\Actions\ActionEvent as BaseActionEvent;

/**
 * Custom ActionEvent model to allow string model keys.
 *
 * https://github.com/laravel/nova-issues/issues/141#issuecomment-755358308
 */
class ActionEvent extends BaseActionEvent
{
    /**
     * Prune the action events for the given types.
     *
     * @param  \Illuminate\Support\Collection<int,array<string,int>>  $models
     * @param  int  $limit
     */
    public static function prune($models, $limit = 25): void
    {
        $models = $models->map(static function (array $model): array {
            $model['actionable_id'] = (string) $model['actionable_id'];
            $model['target_id'] = (string) $model['target_id'];
            $model['model_id'] = (string) $model['model_id'];

            return $model;
        });

        parent::prune($models, $limit);
    }
}
