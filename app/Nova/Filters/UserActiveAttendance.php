<?php

declare(strict_types=1);

namespace App\Nova\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

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
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>  $query
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Attendance>
     */
    #[\Override]
    public function apply(NovaRequest $request, $query, $value): Builder
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
    #[\Override]
    public function options(Request $request): array
    {
        return [
            'Yes' => 'yes',
            'No' => 'no',
        ];
    }
}
