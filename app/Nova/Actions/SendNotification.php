<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Str;
use App\NotificationTemplate;
use App\Mail\DatabaseMailable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Laravel\Nova\Fields\ActionFields;

class SendNotification extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        foreach ($models as $model) {
            Mail::to($model->gt_email)->send(new DatabaseMailable($fields->template, null));
        }

        return Action::message(Str::plural('Email', count($models)).' sent!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        $query = NotificationTemplate::all();

        $templates_for_select = [];

        foreach ($query as $item) {
            $templates_for_select[$item->id] = $item->name;
        }

        return [
            Select::make('Notification Template', 'template')
                ->options($templates_for_select)
                ->displayUsingLabels()
                ->creationRules('required'),
        ];
    }
}
