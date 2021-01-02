<?php

declare(strict_types=1);

namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use Outhebox\NovaHiddenField\HiddenField as Hidden;

/**
 * A Nova resource for signatures.
 *
 * @property bool $electronic
 * @property ?string $scanned_agreement
 * @property \Carbon\Carbon $updated_at
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
    public static $group = 'Membership Agreements';

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

            ...(! $this->electronic ? [
                new Panel(
                    'Paper Upload',
                    [
                        File::make('Scanned Agreement')
                            ->help('Upload the entire agreement as a single file.')
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
            ] : []),

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
            ] : []),

            self::metadataPanel(),

            Hidden::make('Complete')
                ->defaultValue('1')
                ->onlyOnForms(),

            Hidden::make('Uploaded By', 'uploaded_by')
                ->defaultValue(strval($request->user()->id))
                ->onlyOnForms(),
        ];
    }
}
