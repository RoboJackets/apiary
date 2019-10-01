<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UserActive extends Filter
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Active';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Illuminate\Database\Eloquent\Builder  $query
     * @param string  $value
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Chelout\RelationshipEvents\BelongsToMany
     */
    public function apply(Request $request, $query, $value)
    {
        return 'yes' === $value ? $query->active() : $query->inactive();
    }

    /**
     * Get the filter's available options.
     *
     * @param \Illuminate\Http\Request  $request
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
