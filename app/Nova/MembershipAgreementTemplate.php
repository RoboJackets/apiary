<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Markdown;

/**
 * A Nova resource for membership agreement templates.
 *
 * @extends \App\Nova\Resource<\App\Models\MembershipAgreementTemplate>
 */
class MembershipAgreementTemplate extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\MembershipAgreementTemplate::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'updated_at';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Agreements';

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Templates';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'Template';
    }

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('ID')
                ->sortable(),

            DateTime::make('Revision Date', 'updated_at')
                ->sortable()
                ->exceptOnForms(),

            Markdown::make('Text')
                ->alwaysShow(),

            HasMany::make('Signatures'),
        ];
    }

    public static function searchable(): bool
    {
        return false;
    }
}
