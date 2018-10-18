<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UserActiveAttendance extends Filter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'User Active';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value === 'yes') {
            return $query->whereHas('attendee', function ($q) {
                $q->active();
            });
        } else {
            return $query->whereDoesntHave('attendee', function ($q) {
                $q->active();
            });
        }
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Yes' => 'yes',
            'No' => 'no',
        ];
    }
}
