<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for signatures.
 *
 * @extends \App\Nova\Resource<\App\Models\Signature>
 */
class Signature extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Signature::class;

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
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
    {
        return [
            ID::make('ID')
                ->sortable(),

            BelongsTo::make('User')
                ->readonly(),

            BelongsTo::make('Template', 'membershipAgreementTemplate', MembershipAgreementTemplate::class)
                ->readonly(),

            Boolean::make('Complete')
                ->exceptOnForms(),

            Text::make('Type', 'electronic')
                ->resolveUsing(static function (bool $electronic): string {
                    return $electronic ? 'Electronic' : 'Paper';
                })
                ->exceptOnForms(),

            DateTime::make('Rendered', 'render_timestamp')
                ->onlyOnDetail(),

            ...($this->electronic ? [
                new Panel(
                    'Electronic Signature',
                    [
                        Text::make('CAS Host')
                            ->onlyOnDetail(),

                        Text::make('CAS Service URL Hash')
                            ->onlyOnDetail(),

                        Text::make('CAS Ticket')
                            ->onlyOnDetail(),

                        Text::make('IP Address')
                            ->onlyOnDetail(),

                        Code::make('IP Address Location Estimate')
                            ->json()
                            ->onlyOnDetail(),

                        Text::make('User Agent')
                            ->onlyOnDetail(),

                        DateTime::make('Redirected to CAS', 'redirect_to_cas_timestamp')
                            ->onlyOnDetail(),

                        DateTime::make('CAS Ticket Redeemed', 'cas_ticket_redeemed_timestamp')
                            ->onlyOnDetail(),
                    ]
                ),
            ] : [
                new Panel(
                    'Paper Upload',
                    [
                        File::make('Scanned Agreement')
                            ->help(
                                'Upload the entire agreement as a single file. Verify that the revision date at the top'
                                .' of the document matches the revision date shown above.'
                            )
                            ->disk('local')
                            ->deletable(false)
                            ->required()
                            ->rules('required'),

                        BelongsTo::make('Uploaded By', 'uploadedBy', User::class)
                            ->onlyOnDetail(),

                        DateTime::make('Uploaded At', 'updated_at')
                            ->resolveUsing(function (Carbon $str): ?Carbon {
                                return null === $this->scanned_agreement ? null : $this->updated_at;
                            })
                            ->onlyOnDetail(),
                    ]
                ),
            ]),

            MorphMany::make('DocuSign Envelopes', 'envelope', DocuSignEnvelope::class)
                ->onlyOnDetail(),

            self::metadataPanel(),
        ];
    }
}
