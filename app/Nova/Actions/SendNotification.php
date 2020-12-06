<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use App\Mail\DatabaseMailable;
use App\Models\NotificationTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class SendNotification extends Action
{
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
            Mail::to($model->gt_email)->queue((new DatabaseMailable(intval($fields->template), []))->onQueue('email'));
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
