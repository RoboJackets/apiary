<?php

declare(strict_types=1);

namespace App\Nova;

use App\Nova\Actions\VoidDocuSignEnvelope;
use App\Policies\DocuSignEnvelopePolicy;
use Illuminate\Http\Request;
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
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return 'DocuSign Envelopes';
    }

    /**
     * Get the displayable singular label of the resource.
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
     * The relationships that should be eager loaded on index queries.
     *
     * @var array<string>
     */
    public static $with = [
        'signable',
        'signedBy',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Signed By', 'signedBy', User::class),

            MorphTo::make('Signed For', 'signable')
                ->types([
                    TravelAssignment::class,
                    Signature::class,
                ]),

            Boolean::make('Complete'),

            Boolean::make('Acknowledgement Sent')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            Text::make('DocuSign Envelope ID', 'envelope_id')
                ->onlyOnDetail()
                ->copyable(),

            URL::make('View in DocuSign', 'sender_view_url')
                ->displayUsing(static fn () => 'Sender View')
                ->onlyOnDetail(),

            ...($this->sent_by === null ? [] : [
                BelongsTo::make('Sent By', 'sentBy', User::class)
                    ->onlyOnDetail(),
            ]),

            ...($this->signer_ip_address === null ? [] : [
                Text::make('IP Address', 'signer_ip_address')
                    ->onlyOnDetail(),
            ]),

            Panel::make('Documents', [
                ...($this->membership_agreement_filename === null ? [] : [
                    File::make('Membership Agreement', 'membership_agreement_filename')->disk('local'),
                ]),

                ...($this->travel_authority_filename === null ? [] : [
                    File::make('Travel Information Form', 'travel_authority_filename')->disk('local'),
                ]),

                ...($this->covid_risk_filename === null ? [] : [
                    File::make('COVID Risk Acknowledgement', 'covid_risk_filename')->disk('local'),
                ]),

                ...($this->direct_bill_airfare_filename === null ? [] : [
                    File::make('Direct Bill Airfare Request', 'direct_bill_airfare_filename')->disk('local'),
                ]),

                ...($this->itinerary_request_filename === null ? [] : [
                    File::make('Itinerary Request', 'itinerary_request_filename')->disk('local'),
                ]),

                ...($this->summary_filename === null ? [] : [
                    File::make('Summary', 'summary_filename')->disk('local'),
                ]),
            ]),

            Panel::make('Timestamps', [
                DateTime::make('Created', 'created_at')
                    ->onlyOnDetail(),

                ...($this->sent_at === null ? [] : [
                    DateTime::make('Sent', 'sent_at')
                        ->onlyOnDetail(),
                ]),

                ...($this->viewed_at === null ? [] : [
                    DateTime::make('Viewed', 'viewed_at')
                        ->onlyOnDetail(),
                ]),

                ...($this->signed_at === null ? [] : [
                    DateTime::make('Signed', 'signed_at')
                        ->onlyOnDetail(),
                ]),

                ...($this->completed_at === null ? [] : [
                    DateTime::make('Completed', 'completed_at')
                        ->onlyOnDetail(),
                ]),

                DateTime::make('Updated', 'updated_at')
                    ->onlyOnDetail(),

                ...($this->deleted_at === null ? [] : [
                    DateTime::make('Deleted', 'deleted_at')
                        ->onlyOnDetail(),
                ]),
            ]),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<\Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [
            VoidDocuSignEnvelope::make()
                ->canSee(
                    static function (Request $request): bool {
                        $envelope = \App\Models\DocuSignEnvelope::whereId($request->resourceId ?? $request->resources)
                            ->withTrashed()
                            ->sole();

                        return (new DocuSignEnvelopePolicy())->delete($request->user(), $envelope) &&
                            ! $envelope->complete &&
                            $envelope->envelope_id !== null;
                    }
                )
                ->canRun(
                    static fn (
                        NovaRequest $request,
                        \App\Models\DocuSignEnvelope $envelope
                    ): bool => (new DocuSignEnvelopePolicy())->delete($request->user(), $envelope) &&
                        ! $envelope->complete &&
                        $envelope->envelope_id !== null
                ),
        ];
    }

    public static function searchable(): bool
    {
        return false;
    }
}
