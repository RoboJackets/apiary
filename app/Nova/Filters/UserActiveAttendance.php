<?php declare(strict_types = 1);

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

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
    public function apply(Request $request, Builder $query, string $value): Builder
    {
        if ('yes' === $value) {
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request): array
    {
        return [
            'Yes' => 'yes',
            'No' => 'no',
        ];
    }
}
