<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * Opens a custom modal (the `record-attendance` Vue component) on the Team/Event detail view for
 * recording attendance by GTID or access card number. The modal talks directly to the attendance
 * API, so this action's handle() is not part of the normal flow.
 */
class RecordAttendance extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The frontend component used to render this action. Overriding this replaces Nova's default
     * confirmation modal with our interactive record-attendance modal.
     *
     * @var string
     */
    public $component = 'record-attendance';

    /**
     * Perform the action on the given models.
     *
     * The record-attendance modal submits directly to the attendance API, so this is only reached if
     * the action is somehow run through Nova's normal action pipeline.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Team|\App\Models\Event>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        return Action::danger('Use the Record Attendance window to enter a GTID or access card number.');
    }
}
