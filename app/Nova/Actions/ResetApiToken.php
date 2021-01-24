<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ResetApiToken extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Reset API Token';

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection<\App\Models\User>  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        foreach ($models as $model) {
            $model->api_token = bin2hex(openssl_random_pseudo_bytes(16));
            $model->save();
        }

        return Action::message('The API token'.(1 === count($models) ? ' was' : 's were').' reset!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
