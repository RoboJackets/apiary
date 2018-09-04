<?php

namespace App\Nova;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\MorphToMany;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\\User';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'uid',
        'first_name',
        'last_name',
        'preferred_name',
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

            new Panel('Emergency Contact', $this->emergencyFields()),

            new Panel('Swag', $this->swagFields()),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    protected function basicFields()
    {
        return [
            Text::make('Username', 'uid')
                ->sortable()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Text::make('Preferred First Name')
                ->sortable(),

            Text::make('Legal First Name', 'first_name')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Last Name')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Georgia Tech Email', 'gt_email')
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Text::make('Personal Email')
                ->hideFromIndex()
                ->rules('email', 'max:255', 'nullable')
                ->creationRules('unique:users,personal_email')
                ->updateRules('unique:users,personal_email,{{resourceId}}'),

            Number::make('GTID')
                ->onlyOnDetail()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Text::make('API Token')
                ->onlyOnDetail(),

            Text::make('Phone Number', 'phone')
                ->hideFromIndex(),

            Boolean::make('Active', 'is_active')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
        ];
    }

    protected function emergencyFields()
    {
        return [
            Text::make('Emergency Contact Name')
                ->hideFromIndex(),

            Text::make('Emergency Contact Phone Number', 'emergency_contact_phone')
                ->hideFromIndex(),
        ];
    }

    protected function swagFields()
    {
        $shirt_sizes = [
            's' => 'Small',
            'm' => 'Medium',
            'l' => 'Large',
            'xl' => 'Extra-Large',
            'xxl' => 'XXL',
            'xxxl' => 'XXXL',
        ];

        return [
            Select::make('T-Shirt Size', 'shirt_size')
                ->options($shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex(),

            Select::make('Polo Size')
                ->options($shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex(),
        ];
    }

    protected function metaFields()
    {
        return [
            DateTime::make('Account Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),

            MorphToMany::make('Roles', 'roles', \Vyuldashev\NovaPermission\Role::class),
            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class),
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
        return [
            new Filters\UserType,
        ];
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
        return [];
    }
}
