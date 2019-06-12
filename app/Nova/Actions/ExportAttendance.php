<?php

namespace App\Nova\Actions;

use Laravel\Nova\Fields\Date;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;

class ExportAttendance extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export for CoC';

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    public function __construct()
    {
        /*
        $this->withFilename('RoboJacketsAttendance.csv')
            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ->withChunkCount(10000)
            ->only('gtid');
         */
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        \Log::debug('fdsa'.$fields->start_date.'.'.$fields->end_date);
        \Log::debug($fields);
        return Action::message('Test!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Date::make('Start Date')
                ->format('MM/DD/YYYY')
                ->help('Leave blank for two weeks ago'),

            Date::make('End Date')
                ->format('MM/DD/YYYY')
                ->help('Leave blank for today'),
        ];
    }
}
