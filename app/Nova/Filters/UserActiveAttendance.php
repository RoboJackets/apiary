<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
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
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public function apply(Request $request, $query, $value): Builder
    {
        if ($value === 'yes') {
            return $query->whereHas('attendee', static function (Builder $q): void {
                $q->active();
            });
        }

        return $query->whereDoesntHave('attendee', static function (Builder $q): void {
            $q->active();
        });
    }

    /**
     * Get the filter's available options.
     *
     * @return array<string,string>
     */
    public function options(Request $request): array
    {
        return [
            'Yes' => 'yes',
            'No' => 'no',
        ];
    }
}
