<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;

class RecruitingVisit extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\RecruitingVisit';

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Recruiting Visits';
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return 'Recruiting Visit';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'recruiting_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'recruiting_name',
        'recruiting_email',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            new Panel('Basic Information', $this->basicFields()),

            new Panel('Tracking Information', $this->trackingFields()),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    protected function basicFields()
    {
        return [
            Text::make('Name', 'recruiting_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email', 'recruiting_email')
                ->sortable()
                ->rules('required', 'max:255', 'email'),
        ];
    }

    protected function trackingFields()
    {
        return [
            Text::make('Visit Token')
                ->onlyOnDetail()
                ->rules('required', 'max:255'),

            BelongsTo::make('User'),
        ];
    }

    protected function metaFields()
    {
        return [
            DateTime::make('Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new Actions\SendRecruitingEmail)
                ->canSee(function ($request) {
                    return true;
                })->canRun(function ($request, $user) {
                    return $request->user()->can('send-notifications');
                }),
        ];
    }
}
