<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\RecruitingResponse;
use App\Nova\Actions\SendRecruitingEmail;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for recruiting visits.
 *
 * @property int $id
 */
class RecruitingVisit extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\RecruitingVisit::class;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = ['user'];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'Recruiting Visits';
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
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
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Meetings';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'recruiting_name',
        'recruiting_email',
    ];

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        $this_id = $this->id;

        return [
            Text::make('Name', 'recruiting_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email', 'recruiting_email')
                ->sortable()
                ->rules('required', 'max:255', 'email'),

            Text::make('Survey Responses', static function () use ($this_id): string {
                return implode(', ', RecruitingResponse::where(
                    'recruiting_visit_id',
                    '=',
                    $this_id
                )->pluck('response')->toArray());
            }),

            new Panel('Tracking Information', $this->trackingFields()),

            self::metadataPanel(),
        ];
    }

    /**
     * Tracking fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function trackingFields(): array
    {
        return [
            Text::make('Visit Token')
                ->onlyOnDetail()
                ->rules('required', 'max:255'),

            BelongsTo::make('User')
                ->nullable()
                ->searchable(),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(Request $request): array
    {
        return [
            new SendRecruitingEmail(),
        ];
    }
}
