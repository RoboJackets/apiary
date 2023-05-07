<?php

declare(strict_types=1);

namespace App\Nova;

use Adldap\Laravel\Facades\Adldap;
use App\Models\DuesTransaction as AppModelsDuesTransaction;
use App\Models\User as AppModelsUser;
use App\Nova\Actions\CreateOAuth2Client;
use App\Nova\Actions\CreatePersonalAccessToken;
use App\Nova\Actions\RevokeOAuth2Tokens;
use App\Nova\Metrics\CreateReasonBreakdown;
use App\Nova\Metrics\ResumesSubmitted;
use App\Nova\Metrics\TotalAttendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext;

/**
 * A Nova resource for users.
 *
 * @extends \App\Nova\Resource<\App\Models\User>
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = AppModelsUser::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Other';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'uid',
        'first_name',
        'last_name',
        'preferred_name',
        'gtid',
        'github_username',
        'phone',
        'gt_email',
        'clickup_email',
        'gmail_address',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Username', 'uid')
                ->sortable()
                ->rules('required', 'max:127')
                ->creationRules('unique:users,uid')
                ->updateRules('unique:users,uid,{{resourceId}}'),

            Text::make('Preferred First Name')
                ->rules('nullable', 'max:127'),

            Text::make('Legal First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:127'),

            Text::make('Last Name')
                ->sortable()
                ->rules('required', 'max:127'),

            Text::make('GTED Primary Affiliation', 'primary_affiliation')
                ->displayUsing(
                    static fn (?string $a): ?string => $a === null || $a === 'member' ? null : ucfirst($a)
                )
                ->rules('required')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            Text::make('Georgia Tech Email', 'gt_email')
                ->rules('required', 'email')
                ->creationRules('unique:users,gt_email')
                ->updateRules('unique:users,gt_email,{{resourceId}}'),

            Text::make('Email Suppression Reason')
                ->onlyOnDetail(),

            Number::make('GTID')
                ->hideFromIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-gtid'))
                ->rules('required', 'integer', 'min:900000000', 'max:999999999')
                ->creationRules('unique:users,gtid')
                ->updateRules('unique:users,gtid,{{resourceId}}'),

            Text::make('Phone Number', 'phone')
                ->hideFromIndex()
                ->rules('nullable', 'max:15'),

            Boolean::make('Active', 'is_active')
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Boolean::make('Latest Agreement Signed', 'signed_latest_agreement')
                ->onlyOnDetail(),

            URL::make('Manager', static fn (AppModelsUser $user): ?string => $user->manager === null ? null : route(
                'nova.pages.detail',
                [
                    'resource' => self::uriKey(),
                    'resourceId' => $user->manager->id,
                ]
            ))
                ->displayUsing(fn (): ?string => $this->manager?->name)
                ->onlyOnDetail(),

            HasMany::make('Signatures'),

            new Panel(
                'Parent or Guardian Signature',
                [
                    Text::make('Parent or Guardian Name', 'parent_guardian_name')
                        ->rules('required_with:parent_guardian_email')
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                    Text::make('Parent or Guardian Email', 'parent_guardian_email')
                        ->rules('required_with:parent_guardian_name', 'email:rfc,strict,dns,spoof')
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),
                ]
            ),

            new Panel(
                'System Access',
                [
                    Boolean::make('Active', 'is_access_active')
                        ->onlyOnDetail(),

                    DateTime::make('Override Expiration', 'access_override_until')
                        ->onlyOnDetail(),

                    BelongsTo::make('Override Entered By', 'accessOverrideBy', self::class)
                        ->onlyOnDetail(),

                    Text::make('Self-Service Override', static function (AppModelsUser $user) {
                        if ($user->has_active_override && $user->access_override_by_id === $user->id) {
                            return 'Active';
                        }

                        return (string) $user->self_service_override_eligibility;
                    })->hideFromIndex(),

                    Boolean::make('BuzzCard Access Opt-Out', 'buzzcard_access_opt_out')
                        ->hideFromIndex(),
                ]
            ),

            new Panel(
                'Linked Accounts',
                [
                    Text::make('GitHub', 'github_username')
                        ->hideFromIndex()
                        ->rules('nullable', 'max:39')
                        ->creationRules('unique:users,github_username')
                        ->updateRules('unique:users,github_username,{{resourceId}}'),

                    Boolean::make('GitHub Invite Pending')
                        ->hideFromIndex()
                        ->help('Generally this is managed by Jedi, but can be manually overridden here if necessary.'
                            .' This controls whether a card is displayed but not the user\'s actual access.'),

                    Text::make('Google', 'gmail_address')
                        ->hideFromIndex()
                        ->rules('nullable', 'max:255', 'email')
                        ->creationRules('unique:users,gmail_address')
                        ->updateRules('unique:users,gmail_address,{{resourceId}}'),

                    Text::make('ClickUp', 'clickup_email')
                        ->hideFromIndex()
                        ->rules('nullable', 'max:255', 'email')
                        ->creationRules('unique:users,clickup_email')
                        ->updateRules('unique:users,clickup_email,{{resourceId}}'),

                    Boolean::make('ClickUp Invite Pending', 'clickup_invite_pending')
                        ->hideFromIndex()
                        ->help('This flag is set by JEDI but may be out of sync with ClickUp in some cases.'
                            .' It only controls UX elements.'),

                    Boolean::make('SUMS', 'exists_in_sums')
                        ->hideFromIndex()
                        ->help(
                            'This flag is set by JEDI and should not be modified unless you know what you are doing.'
                            .' It only controls UX elements.'
                        ),
                ]
            ),

            new Panel(
                'Resume',
                [
                    File::make(
                        'Resume',
                        fn (): ?string => $this->resume_date !== null ? 'resumes/'.$this->uid.'.pdf' : null
                    )->path(
                        'resumes'
                    )
                        ->disk('local')
                        ->deletable(false)
                        ->onlyOnDetail()
                        ->canSee(static function (Request $request): bool {
                            if ($request->resourceId === $request->user()->id) {
                                return true;
                            }

                            return $request->user()->can('read-users-resume');
                        }),

                    DateTime::make('Resume Uploaded At', 'resume_date')
                        ->onlyOnDetail(),
                ]
            ),

            new Panel('Emergency Contact', $this->emergencyFields()),

            new Panel('Swag', $this->swagFields()),

            HasMany::make('Dues Transactions', 'duesTransactions', DuesTransaction::class)
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-dues-transactions-own');
                    }

                    return $request->user()->can('read-dues-transactions');
                }),

            HasMany::make('Travel Assignments', 'assignments', TravelAssignment::class),

            HasMany::make('DocuSign Envelopes', 'envelopes', DocuSignEnvelope::class),

            BelongsToMany::make('Teams')
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-teams-membership-own');
                    }

                    return $request->user()->can('read-teams-membership');
                }),

            HasMany::make('Attendance')
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-attendance-own');
                    }

                    return $request->user()->can('read-attendance');
                }),

            BelongsToMany::make('Majors')
                ->readonly(static fn (Request $request): bool => ! $request->user()->hasRole('admin')),

            BelongsToMany::make('Class Standing', 'classStanding')
                ->readonly(static fn (Request $request): bool => ! $request->user()->hasRole('admin')),

            HasMany::make('OAuth2 Clients', 'clients')
                ->canSee(
                    static fn (Request $request): bool => $request->user()->hasRole(
                        'admin'
                    ) || $request->resourceId === $request->user()->id
                ),

            HasMany::make('OAuth2 Access Tokens', 'tokens', OAuth2AccessToken::class)
                ->canSee(
                    static fn (Request $request): bool => $request->user()->hasRole(
                        'admin'
                    ) || $request->resourceId === $request->user()->id
                ),

            new Panel('Employment', [
                Number::make('Employee ID (OneUSG)', 'employee_id')
                    ->onlyOnDetail()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                Text::make('Home Department (BuzzAPI)', 'employee_home_department')
                    ->onlyOnDetail()
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                ...(config('features.whitepages') === true ? [
                    Text::make('Home Department (Whitepages)', static function (AppModelsUser $user): ?string {
                        $uid = $user->uid;

                        return Cache::remember(
                            'home_department_'.$uid,
                            now()->addDay(),
                            static function () use ($uid): ?string {
                                $parentSpan = SentrySdk::getCurrentHub()->getSpan();

                                if ($parentSpan !== null) {
                                    $context = new SpanContext();
                                    $context->setOp('ldap.get_home_department');
                                    $span = $parentSpan->startChild($context);
                                    SentrySdk::getCurrentHub()->setSpan($span);
                                }

                                $result = Adldap::search()
                                    ->where('uid', '=', $uid)
                                    ->where('employeeType', '=', 'employee')
                                    ->select('ou')
                                    ->get()
                                    ->pluck('ou')
                                    ->toArray();

                                if ($parentSpan !== null) {
                                    // @phan-suppress-next-line PhanPossiblyUndeclaredVariable
                                    $span->finish();
                                    SentrySdk::getCurrentHub()->setSpan($parentSpan);
                                }

                                return $result === [] ? null : $result[0][0];
                            }
                        );
                    })
                        ->onlyOnDetail()
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),
                ] : []),
            ]),

            new Panel('Metadata', $this->metaFields()),
        ];
    }

    /**
     * Emergency contact information.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function emergencyFields(): array
    {
        return [
            Text::make('Emergency Contact Name')
                ->hideFromIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-emergency_contact')),

            Text::make('Emergency Contact Phone Number', 'emergency_contact_phone')
                ->hideFromIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-emergency_contact')),
        ];
    }

    /**
     * Swag information.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function swagFields(): array
    {
        return [
            Select::make('T-Shirt Size', 'shirt_size')
                ->options(AppModelsUser::$shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex(),

            Select::make('Polo Size')
                ->options(AppModelsUser::$shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex(),
        ];
    }

    /**
     * Timestamp fields.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    protected function metaFields(): array
    {
        return [
            DateTime::make('Account Created', 'created_at')
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->onlyOnDetail(),

            Boolean::make('Has Ever Logged In')
                ->hideFromIndex()
                ->required(),

            Boolean::make('Is Service Account')
                ->hideFromIndex(),

            Text::make('Create Reason')
                ->hideFromIndex()
                ->required()
                ->rules('required'),

            Text::make('gtDirGUID', 'gtDirGUID')
                ->hideFromIndex(),

            MorphToMany::make('Roles', 'roles', \Vyuldashev\NovaPermission\Role::class)
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class)
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            (new TotalAttendance())
                ->onlyOnDetail()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-attendance')),
            (new ResumesSubmitted())
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-resume')),
            new CreateReasonBreakdown(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new Filters\UserActive(),
            new Filters\UserAccessActive(),
            new Filters\UserType(),
            new Filters\UserTeam(),
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
            (new Actions\SyncAccess())
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->hasRole('admin')
                )->confirmButtonText('Sync Access'),
            (new Actions\OverrideAccess())
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->hasRole('admin')
                )->confirmButtonText('Override Access'),
            resolve(CreatePersonalAccessToken::class)
                ->canSee(static fn (Request $request): bool => true)
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->hasRole(
                        'admin'
                    ) || ($request->user()->id === $user->id)
                )->confirmButtonText('Create Access Token'),
            resolve(CreateOAuth2Client::class)
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->hasRole('admin')
                )->confirmButtonText('Create Client'),
            resolve(RevokeOAuth2Tokens::class)
                ->canSee(static fn (Request $request): bool => true)
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->hasRole(
                        'admin'
                    ) || ($request->user()->id === $user->id)
                )->confirmButtonText('Revoke Tokens'),
            (new Actions\ExportResumes())
                ->standalone()
                ->onlyOnIndex()
                ->canSee(
                    static fn (Request $request): bool => $request->user()->can('read-users-resume')
                )->confirmButtonText('Export Resumes'),
            (new Actions\RefreshFromGTED())
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->hasRole('admin')
                )->confirmButtonText('Refresh from GTED'),
            (new Actions\ExportUsersBuzzCardAccess())
                ->standalone()
                ->onlyOnIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-gtid'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->can(
                        'read-users-gtid'
                    )
                )->confirmButtonText('Export List'),
            (new Actions\ExportUsernames())
                ->onlyOnIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->can('read-users')
                ),
            (new Actions\ExportDemographicsSurveyRecipients())
                ->standalone()
                ->onlyOnIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users'))
                ->canRun(
                    static fn (NovaRequest $request, AppModelsUser $user): bool => $request->user()->can('read-users')
                ),
        ];
    }

    /**
     * Only show relevant users for relatable queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        if ($request->is('nova-api/dues-transactions/*')) {
            return $query->inactive()->orWhere('users.id', '=', $request->viaResourceId);
        }

        if ($request->is('nova-api/travel-assignments/*')) {
            return $query->accessActive()->orWhere('users.id', '=', $request->viaResourceId);
        }

        return $query;
    }

    /**
     * Get the search result subtitle for the resource.
     */
    public function subtitle(): ?string
    {
        $managed_team_names = $this->manages()->pluck('name');

        if (count($managed_team_names) > 0) {
            $team_name = $managed_team_names[0];

            if ($team_name === 'Core') {
                return 'President';
            }

            if ($team_name === 'Alumni Leadership') {
                return 'Corporation President';
            }

            if (
                str_contains($team_name, 'Training') ||
                str_contains($team_name, 'Core') ||
                str_contains($team_name, 'Outreach')
            ) {
                return $team_name.' Chair';
            }

            return $team_name.' Project Manager';
        }

        // This query is adapted from the dashboard controller
        $paidTransactions = AppModelsDuesTransaction::select(
            'dues_transactions.id',
            'dues_transactions.dues_package_id',
            'dues_packages.effective_start',
            'dues_packages.effective_end'
        )
            ->leftJoin('payments', static function (JoinClause $join): void {
                $join->on('dues_transactions.id', '=', 'payable_id')
                    ->where('payments.payable_type', AppModelsDuesTransaction::getMorphClassStatic())
                    ->where('payments.amount', '>', 0);
            })
            ->leftJoin('dues_packages', 'dues_transactions.dues_package_id', '=', 'dues_packages.id')
            ->where('user_id', $this->id)
            ->whereNotNull('payments.id')
            ->orderBy('dues_packages.effective_start')
            ->orderBy('dues_packages.effective_end')
            ->get();

        $firstPaidTransact = $paidTransactions->first();
        $lastPaidTransact = $paidTransactions->last();

        if ($firstPaidTransact !== null && $lastPaidTransact !== null) {
            $firstYear = (new Carbon($firstPaidTransact->effective_start, 'America/New_York'))->year;
            $lastYear = (new Carbon($lastPaidTransact->effective_end, 'America/New_York'))->year;

            return $firstYear === $lastYear ? 'Member '.$firstYear : 'Member '.$firstYear.'-'.$lastYear;
        }

        $major_names = $this->majors()->pluck('display_name')->toArray();

        $class_standing_names = $this->classStanding()->pluck('name')->toArray();

        if (count($major_names) > 0 && $major_names[0] !== null && count($class_standing_names) > 0) {
            return $major_names[0].' | '.ucfirst($class_standing_names[0]);
        }

        return null;
    }
}
