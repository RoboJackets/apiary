<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.ControlStructures.RequireTernaryOperator.TernaryOperatorNotUsed

namespace App\Nova;

use App\Models\DuesTransaction as AppModelsDuesTransaction;
use App\Models\User as AppModelsUser;
use App\Nova\Actions\CreatePersonalAccessToken;
use App\Nova\Actions\ExportDemographicsSurveyRecipients;
use App\Nova\Actions\ExportFilteredResumes;
use App\Nova\Actions\ExportFullYearResumes;
use App\Nova\Actions\ExportUsersBuzzCardAccess;
use App\Nova\Actions\OverrideAccess;
use App\Nova\Actions\RefreshFromGTED;
use App\Nova\Actions\RevokeOAuth2Tokens;
use App\Nova\Actions\SyncAccess;
use App\Nova\Actions\SyncInactiveAccess;
use App\Nova\Metrics\CreateReasonBreakdown;
use App\Nova\Metrics\ResumesSubmitted;
use App\Nova\Metrics\TotalAttendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Spatie\Permission\Models\Role;

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
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 5;

    /**
     * The number of results to display when searching the resource using Scout.
     *
     * @var int
     */
    public static $scoutSearchResults = 5;

    /**
     * Get the fields displayed by the resource.
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Hidden::make('Has Ever Logged In')
                ->showOnCreating()
                ->default(static fn (Request $r): int => 0),

            Text::make('Username', 'uid')
                ->sortable()
                ->rules('required', 'max:127')
                ->creationRules('unique:users,uid')
                ->updateRules('unique:users,uid,{{resourceId}}')
                ->copyable(),

            Text::make('Preferred First Name')
                ->hideWhenCreating()
                ->rules('nullable', 'max:127')
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Text::make('Legal First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:127'),

            Text::make('Legal Middle Name', 'legal_middle_name')
                ->sortable()
                ->rules('nullable', 'max:127')
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Text::make('Legal Last Name', 'last_name')
                ->sortable()
                ->rules('required', 'max:127'),

            Text::make('GTED Primary Affiliation', 'primary_affiliation')
                ->hideWhenCreating()
                ->displayUsing(
                    static fn (?string $a): ?string => $a === null || $a === 'member' ? null : ucfirst($a)
                )
                ->rules('required')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Email::make('Georgia Tech Email', 'gt_email')
                ->rules('required', 'email:rfc,strict,dns,spoof')
                ->creationRules('unique:users,gt_email')
                ->updateRules('unique:users,gt_email,{{resourceId}}')
                ->copyable(),

            Text::make('Email Suppression Reason')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Number::make('GTID')
                ->hideFromIndex()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-gtid'))
                ->rules('required', 'integer', 'min:900000000', 'max:999999999')
                ->creationRules('unique:users,gtid')
                ->updateRules('unique:users,gtid,{{resourceId}}')
                ->copyable(),

            Text::make('Phone Number', 'phone')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                ->rules('nullable', 'max:15')
                ->copyable(),

            Boolean::make('Phone Number Verified', 'phone_verified')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Boolean::make('Membership Active', 'is_active')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Boolean::make('Latest Agreement Signed', 'signed_latest_agreement')
                ->onlyOnDetail()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Text::make('Graduation Semester', 'human_readable_graduation_semester')
                ->onlyOnDetail()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            new Panel(
                'Air Travel',
                [
                    Select::make('Legal Gender')
                        ->options([
                            'M' => 'Male (M)',
                            'F' => 'Female (F)',
                            'X' => 'Unspecified (X)',
                            'U' => 'Undisclosed (U)',
                        ])
                        ->displayUsingLabels()
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                    Date::make('Date of Birth')
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                    Text::make('Delta SkyMiles Number', 'delta_skymiles_number')
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),
                ]
            ),

            HasMany::make('Signatures')
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            new Panel(
                'Parent or Guardian Signature',
                [
                    Text::make('Parent or Guardian Name', 'parent_guardian_name')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->rules('required_with:parent_guardian_email', 'nullable')
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                    Email::make('Parent or Guardian Email', 'parent_guardian_email')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->rules('required_with:parent_guardian_name', 'email:rfc,strict,dns,spoof', 'nullable')
                        ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                        ->copyable(),
                ]
            ),

            new Panel(
                'System Access',
                [
                    Boolean::make('Active', 'is_access_active')
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

                    DateTime::make('Override Expiration', 'access_override_until')
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

                    BelongsTo::make('Override Entered By', 'accessOverrideBy', self::class)
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

                    BooleanGroup::make('Self-Service Override Criteria', static function (AppModelsUser $user): array {
                        $eligibility = $user->self_service_override_eligibility;

                        return array_merge($eligibility->required_conditions, $eligibility->required_tasks);
                    })->options([
                        'Access must not be active' => 'Access must not be active',
                        'Must have no prior dues payments' => 'Must have no prior dues payments',
                        'Must have no previous access override' => 'Must have no previous access override',
                        'Future dues package must exist' => 'Future dues package must exist',
                        'Sign the membership agreement' => 'Sign the membership agreement',
                        'Attend a team meeting' => 'Attend a team meeting',
                    ])
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

                    Boolean::make('BuzzCard Access Opt-Out', 'buzzcard_access_opt_out')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),
                ]
            ),

            new Panel(
                'Organization Hierarchy',
                [
                    URL::make('Manager', static fn (
                        AppModelsUser $user
                    ): ?string => $user->manager === null ? null : route(
                        'nova.pages.detail',
                        [
                            'resource' => self::uriKey(),
                            'resourceId' => $user->manager->id,
                        ]
                    ))
                        ->displayUsing(fn (): ?string => $this->manager?->name)
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

                    Stack::make('Primary Team', [
                        URL::make('Primary Team', static fn (
                            AppModelsUser $user
                        ): ?string => $user->primaryTeam === null ? null : route(
                            'nova.pages.detail',
                            [
                                'resource' => Team::uriKey(),
                                'resourceId' => $user->primaryTeam->id,
                            ]
                        ))
                            ->displayUsing(fn (): ?string => $this->primaryTeam?->name)
                            ->onlyOnDetail()
                            ->textAlign('left')
                            ->hideFromDetail(
                                static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account
                            ),

                        Text::make(
                            'Primary Team Membership',
                            static fn (AppModelsUser $user): string => view(
                                'nova.partials.user.primaryteam',
                                ['user' => $user]
                            )->render()
                        )
                            ->asHtml()
                            ->onlyOnDetail()
                            ->showOnDetail(
                                static fn (
                                    NovaRequest $request,
                                    AppModelsUser $user
                                ): bool => $user->primaryTeam !== null &&
                                    $user->teams()->where(
                                        'teams.id',
                                        '=',
                                        $user->primaryTeam->id
                                    )->doesntExist()
                            ),
                    ])
                        ->hideFromDetail(
                            static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account
                        ),
                ]
            ),

            new Panel(
                'Linked Accounts',
                [
                    Text::make('GitHub', 'github_username')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->rules('nullable', 'max:39')
                        ->creationRules('unique:users,github_username')
                        ->updateRules('unique:users,github_username,{{resourceId}}')
                        ->copyable(),

                    Boolean::make('GitHub Invite Pending')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->help('Generally this is managed by Jedi, but can be manually overridden here if necessary.'
                            .' This controls whether a card is displayed but not the user\'s actual access.'),

                    Email::make('Google', 'gmail_address')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->rules('nullable', 'max:255', 'email:rfc,strict,dns,spoof')
                        ->creationRules('unique:users,gmail_address')
                        ->updateRules('unique:users,gmail_address,{{resourceId}}')
                        ->copyable(),

                    Email::make('ClickUp', 'clickup_email')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->rules('nullable', 'max:255', 'email:rfc,strict,dns,spoof')
                        ->creationRules('unique:users,clickup_email')
                        ->updateRules('unique:users,clickup_email,{{resourceId}}')
                        ->copyable(),

                    Boolean::make('ClickUp Invite Pending', 'clickup_invite_pending')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->help('This flag is set by JEDI but may be out of sync with ClickUp in some cases.'
                            .' It only controls UX elements.'),

                    Boolean::make('SUMS', 'exists_in_sums')
                        ->hideWhenCreating()
                        ->hideFromIndex()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->help(
                            'This flag is set by JEDI and should not be modified unless you know what you are doing.'
                            .' It only controls UX elements.'
                        ),

                    Text::make(
                        'DocuSign',
                        static fn (\App\Models\User $user): string => view(
                            'nova.partials.user.docusignstatus',
                            ['user' => $user]
                        )->render()
                    )
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->asHtml(),
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
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                        ->canSee(static function (Request $request): bool {
                            if ($request->resourceId === $request->user()->id) {
                                return true;
                            }

                            return $request->user()->can('read-users-resume');
                        }),

                    DateTime::make('Resume Uploaded At', 'resume_date')
                        ->onlyOnDetail()
                        ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),
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
                })
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            HasMany::make('Trip Assignments', 'assignments', TravelAssignment::class)
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            HasMany::make('DocuSign Envelopes', 'envelopes', DocuSignEnvelope::class)
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            BelongsToMany::make('Teams')
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-teams-membership-own');
                    }

                    return $request->user()->can('read-teams-membership');
                })
                ->withoutTrashed()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            HasMany::make('Access Cards')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            HasMany::make('Attendance')
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-attendance-own');
                    }

                    return $request->user()->can('read-attendance');
                })
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            BelongsToMany::make('Majors')
                ->readonly(static fn (Request $request): bool => ! $request->user()->hasRole('admin'))
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            BelongsToMany::make('Class Standing', 'classStanding')
                ->readonly(static fn (Request $request): bool => ! $request->user()->hasRole('admin'))
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            MorphMany::make('OAuth Clients', 'oauthApps', OAuth2Client::class)
                ->canSee(
                    static fn (Request $request): bool => $request->user()->hasRole(
                        'admin'
                    ) || $request->resourceId === $request->user()->id
                ),

            HasMany::make('OAuth Tokens', 'tokens', OAuth2AccessToken::class)
                ->canSee(
                    static fn (Request $request): bool => $request->user()->hasRole(
                            'admin'
                        ) || $request->resourceId === $request->user()->id
                ),

            MorphMany::make('Notifications', 'novaNotifications', Notification::class)
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            new Panel('Employment', [
                Number::make('Employee ID (OneUSG)', 'employee_id')
                    ->onlyOnDetail()
                    ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),

                Text::make('Home Department (BuzzAPI)', 'employee_home_department')
                    ->onlyOnDetail()
                    ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                    ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin')),
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
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-emergency_contact')),

            Text::make('Emergency Contact Phone Number', 'emergency_contact_phone')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account)
                ->canSee(static fn (Request $request): bool => $request->user()->can('read-users-emergency_contact'))
                ->copyable(),

            Boolean::make('Emergency Contact Phone Number Verified', 'emergency_contact_phone_verified')
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideWhenUpdating()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),
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
                ->hideWhenCreating()
                ->options(AppModelsUser::$shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Select::make('Polo Size')
                ->hideWhenCreating()
                ->options(AppModelsUser::$shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),
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
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->onlyOnDetail(),

            DateTime::make('Last Updated', 'updated_at')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->onlyOnDetail(),

            Boolean::make('Has Ever Logged In')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->onlyOnDetail()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Boolean::make('Is Service Account')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideFromIndex(),

            Text::make('Create Reason')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideWhenUpdating()
                ->hideFromIndex()
                ->required()
                ->rules('required')
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

            Text::make('gtDirGUID', 'gtDirGUID')
                ->canSee(static fn (Request $request): bool => $request->user()->hasRole('admin'))
                ->hideWhenCreating()
                ->hideFromIndex()
                ->copyable()
                ->hideFromDetail(static fn (NovaRequest $r, AppModelsUser $u): bool => $u->is_service_account),

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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function actions(NovaRequest $request): array
    {
        $user = AppModelsUser::whereId($request->resourceId ?? $request->resources)->withTrashed()->first();

        if (self::adminCanSee($request) && $user !== null && ! $user->is_service_account) {
            $overrideAccess = [
                OverrideAccess::make()
                    ->canSee(static fn (Request $r): bool => self::adminCanSee($r))
                    ->canRun(static fn (NovaRequest $r, AppModelsUser $u): bool => self::adminCanRun($r)),
            ];

            if ($user->id === $request->user()->id) {
                $overrideAccess = [
                    Action::danger(
                        OverrideAccess::make()->name(),
                        'You cannot override your own access.'
                    )
                        ->withoutConfirmation()
                        ->withoutActionEvents()
                        ->canRun(static fn (): bool => true),
                ];
            } elseif ($user->signed_latest_agreement !== true) {
                $overrideAccess = [
                    Action::danger(
                        OverrideAccess::make()->name(),
                        'This member has not signed the latest membership agreement.'
                    )
                        ->withoutConfirmation()
                        ->withoutActionEvents()
                        ->canRun(static fn (): bool => true),
                ];
            }
        } else {
            $overrideAccess = [];
        }

        if ($user !== null && ! $user->is_service_account) {
            $syncAccess = [
                SyncAccess::make()
                    ->canSee(static fn (Request $r): bool => self::adminOrSelfCanSee($r))
                    ->canRun(static fn (NovaRequest $r, AppModelsUser $u): bool => self::adminOrSelfCanRun($r, $u)),
            ];
        } else {
            $syncAccess = [];
        }

        if ($user !== null && ! $user->is_service_account) {
            $refreshFromGted = [
                RefreshFromGTED::make()
                    ->canSee(static fn (Request $r): bool => self::adminCanSee($r))
                    ->canRun(static fn (NovaRequest $r, AppModelsUser $u): bool => self::adminCanRun($r)),
            ];
        } else {
            $refreshFromGted = [];
        }

        if ($request->user()->can('read-users-resume')) {
            $exportResumes = [
                ExportFilteredResumes::make()
                    ->canSee(static fn (Request $r): bool => $r->user()->can('read-users-resume')),
                ExportFullYearResumes::make()
                    ->canSee(static fn (Request $r): bool => $r->user()->can('read-users-resume')),
            ];
        } else {
            $exportResumes = [
                Action::danger(
                    ExportFilteredResumes::make()->name(),
                    'You do not have access to export resumes.'
                )
                    ->withoutConfirmation()
                    ->withoutActionEvents()
                    ->standalone()
                    ->onlyOnIndex()
                    ->canRun(static fn (): bool => true),
                Action::danger(
                    ExportFullYearResumes::make()->name(),
                    'You do not have access to export resumes.'
                )
                    ->withoutConfirmation()
                    ->withoutActionEvents()
                    ->standalone()
                    ->onlyOnIndex()
                    ->canRun(static fn (): bool => true),
            ];
        }

        if ($request->user()->can('read-users-gtid')) {
            $exportBuzzCardList = [
                ExportUsersBuzzCardAccess::make()
                    ->canSee(static fn (Request $r): bool => $r->user()->can('read-users-gtid')),
            ];
        } else {
            $exportBuzzCardList = [
                Action::danger(
                    ExportUsersBuzzCardAccess::make()->name(),
                    'You do not have access to export BuzzCard access lists.'
                )
                    ->withoutConfirmation()
                    ->withoutActionEvents()
                    ->standalone()
                    ->onlyOnIndex()
                    ->canRun(static fn (): bool => true),
            ];
        }

        if ($request->user()->can('read-users')) {
            $exportDemographicsSurveyList = [
                ExportDemographicsSurveyRecipients::make()
                    ->canSee(static fn (Request $r): bool => $r->user()->can('read-users')),
            ];
        } else {
            $exportDemographicsSurveyList = [
                Action::danger(
                    ExportDemographicsSurveyRecipients::make()->name(),
                    'You do not have access to export demographics survey recipients.'
                )
                    ->withoutConfirmation()
                    ->withoutActionEvents()
                    ->standalone()
                    ->onlyOnIndex()
                    ->canRun(static fn (): bool => true),
            ];
        }

        return [
            SyncInactiveAccess::make()
                ->canSee(static fn (Request $r): bool => self::adminCanSee($r))
                ->canRun(static fn (NovaRequest $r, AppModelsUser $u): bool => self::adminCanRun($r)),

            ...$syncAccess,

            ...$overrideAccess,

            ...$refreshFromGted,

            CreatePersonalAccessToken::make()
                ->canSee(static fn (Request $r): bool => self::adminOrSelfCanSee($r))
                ->canRun(static fn (NovaRequest $r, AppModelsUser $u): bool => self::adminOrSelfCanRun($r, $u)),

            RevokeOAuth2Tokens::make()
                ->canSee(static fn (Request $r): bool => self::adminOrSelfCanSee($r))
                ->canRun(static fn (NovaRequest $r, AppModelsUser $u): bool => self::adminOrSelfCanRun($r, $u)),

            ...$exportResumes,

            ...$exportBuzzCardList,

            ...$exportDemographicsSurveyList,
        ];
    }

    /**
     * Only show relevant users for relatable queries.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\User>
     */
    #[\Override]
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        if ($request->is('nova-api/'.DuesTransaction::uriKey().'/*')) {
            return $query->inactive()->orWhere('users.id', '=', $request->viaResourceId);
        }

        if ($request->is('nova-api/'.TravelAssignment::uriKey().'/*')) {
            return $query->accessActive()->orWhere('users.id', '=', $request->viaResourceId ?? $request->current);
        }

        return $query;
    }

    /**
     * Get the search result subtitle for the resource.
     */
    #[\Override]
    public function subtitle(): ?string
    {
        $managed_team_names = $this->manages()->pluck('name');

        if (count($managed_team_names) > 0) {
            $team_name = $managed_team_names[0];

            if ($team_name === 'Core') {
                return 'President';
            }

            if ($team_name === 'Corporate') {
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
            $firstYear = (new Carbon($firstPaidTransact->effective_start, 'America/New_York'))->addMonth()->year;
            $lastYear = (new Carbon($lastPaidTransact->effective_end, 'America/New_York'))->subMonth()->year;

            return $firstYear === $lastYear ? 'Member '.$firstYear : 'Member '.$firstYear.'-'.$lastYear;
        }

        $major_names = $this->majors()->pluck('display_name')->toArray();

        $class_standing_names = $this->classStanding()->pluck('name')->toArray();

        if (count($major_names) > 0 && $major_names[0] !== null && count($class_standing_names) > 0) {
            return $major_names[0].' | '.ucfirst($class_standing_names[0]);
        }

        return null;
    }

    private static function adminOrSelfCanSee(Request $request): bool
    {
        $targetUserId = $request->resourceId ?? $request->resources;
        $requestingUser = $request->user();

        return $requestingUser->hasRole('admin') || $requestingUser->id === $targetUserId;
    }

    private static function adminOrSelfCanRun(NovaRequest $request, AppModelsUser $user): bool
    {
        return $request->user()->hasRole('admin') || $request->user()->id === $user->id;
    }

    private static function adminCanSee(Request $request): bool
    {
        return $request->user()->hasRole('admin');
    }

    private static function adminCanRun(NovaRequest $request): bool
    {
        return $request->user()->hasRole('admin');
    }

    /**
     * Build a Scout search query for the given resource.
     *
     * @param  \Laravel\Scout\Builder<\App\Models\User>  $query
     * @return \Laravel\Scout\Builder<\App\Models\User>
     */
    #[\Override]
    public static function scoutQuery(NovaRequest $request, $query): \Laravel\Scout\Builder
    {
        if (
            config('scout.driver') === 'meilisearch' && (
                $request->field === 'projectManager' ||
                $request->field === 'primaryContact'
            )
        ) {
            return $query->whereIn(
                'role_id',
                [
                    Role::where('name', '=', 'team-lead')->sole()->id,
                    Role::where('name', '=', 'project-manager')->sole()->id,
                    Role::where('name', '=', 'officer')->sole()->id,
                ]
            );
        }

        return parent::scoutQuery($request, $query);
    }
}
