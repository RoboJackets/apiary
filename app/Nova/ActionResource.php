<?php

declare(strict_types=1);

// @phan-file-suppress PhanGenericConstructorTypes

namespace App\Nova;

use App\Models\ActionEvent;
use Laravel\Nova\Actions\ActionResource as BaseActionResource;

/**
 * Custom ActionEvent resource to allow string model keys.
 *
 * https://github.com/laravel/nova-issues/issues/141#issuecomment-755358308
 *
 * @template TActionModel of \App\Models\ActionEvent
 *
 * @extends \Laravel\Nova\Actions\ActionResource<TActionModel>
 */
class ActionResource extends BaseActionResource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<TActionModel>
     */
    public static $model = ActionEvent::class;
}
