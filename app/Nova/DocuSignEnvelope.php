<?php

declare(strict_types=1);

namespace App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

/**
 * A Nova resource for DocuSign envelopes.
 *
 * @extends \App\Nova\Resource<\App\Models\DocuSignEnvelope>
 */
class DocuSignEnvelope extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\DocuSignEnvelope::class;

    /**
     * Get the displayble label of the resource.
     */
    public static function label(): string
    {
        return 'DocuSign Envelopes';
    }

    /**
     * Get the displayble singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return 'DocuSign Envelope';
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'docusign-envelopes';
    }

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Signed By', 'signedBy', User::class),

            MorphTo::make('Signed For', 'signable')
                ->types([
                    TravelAssignment::class,
                ]),

            Boolean::make('Complete'),

            Text::make('DocuSign Envelope ID', 'envelope_id')
                ->onlyOnDetail(),

            URL::make('DocuSign URL', 'url')
                ->onlyOnDetail(),

            Text::make('IP Address', 'signer_ip_address')
                ->onlyOnDetail(),

            Panel::make('Documents', [
                ...(null === $this->membership_agreement_filename ? [] : [
                    File::make('Membership Agreement', 'membership_agreement_filename')->disk('local'),
                ]),

                ...(null === $this->travel_authority_filename ? [] : [
                    File::make('Travel Authority Request', 'travel_authority_filename')->disk('local'),
                ]),

                ...(null === $this->covid_risk_filename ? [] : [
                    File::make('COVID Risk Acknowledgement', 'covid_risk_filename')->disk('local'),
                ]),

                ...(null === $this->direct_bill_airfare_filename ? [] : [
                    File::make('Direct Bill Airfare Request', 'direct_bill_airfare_filename')->disk('local'),
                ]),

                File::make('Summary', 'summary_filename')->disk('local'),
            ]),

            Panel::make('Timestamps', [
                DateTime::make('Created', 'created_at')
                    ->onlyOnDetail(),

                DateTime::make('Sent', 'sent_at')
                    ->onlyOnDetail(),

                DateTime::make('Viewed', 'viewed_at')
                    ->onlyOnDetail(),

                DateTime::make('Signed', 'signed_at')
                    ->onlyOnDetail(),

                DateTime::make('Completed', 'completed_at')
                    ->onlyOnDetail(),

                DateTime::make('Updated', 'updated_at')
                    ->onlyOnDetail(),

                DateTime::make('Deleted', 'deleted_at')
                    ->onlyOnDetail(),
            ]),
        ];
    }
}
