<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Nova;

use App\Models\User as AppModelsUser;
use App\Nova\Fields\Hidden;
use App\Nova\Metrics\MemberSince;
use App\Nova\Metrics\PrimaryTeam;
use App\Nova\Metrics\ResumesSubmitted;
use App\Nova\Metrics\TotalAttendance;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;

/**
 * A Nova resource for users.
 *
 * @property ?\Carbon\Carbon $resume_date
 * @property string $uid this user's username
 *
 * @method bool hasSignedLatestAgreement()
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
        'autodesk_email',
        'gmail_address',
        'personal_email',
    ];

    /**
     * Get the fields displayed by the resource.
     */
    public function fields(Request $request): array
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
                ->resolveUsing(static function (?string $affiliation): ?string {
                    return null === $affiliation || 'member' === $affiliation ? null : ucfirst($affiliation);
                })
                ->canSee(static function (Request $request): bool {
                    // Hidden to non-admins because it's confusing and not useful
                    return $request->user()->hasRole('admin');
                })
                ->onlyOnDetail(),

            Text::make('Georgia Tech Email', 'gt_email')
                ->rules('required', 'email')
                ->creationRules('unique:users,gt_email')
                ->updateRules('unique:users,gt_email,{{resourceId}}'),

            Text::make('Personal Email')
                ->hideFromIndex()
                ->rules('email', 'max:255', 'nullable')
                ->creationRules('unique:users,personal_email')
                ->updateRules('unique:users,personal_email,{{resourceId}}'),

            Hidden::make('GTID')
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                }),

            // Hidden fields can't be edited, so add this field on the forms so it can be edited for service accounts
            Text::make('GTID')
                ->onlyOnForms()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                })
                ->rules('required', 'integer', 'min:900000000', 'max:999999999')
                ->creationRules('unique:users,gtid')
                ->updateRules('unique:users,gtid,{{resourceId}}'),

            Hidden::make('API Token')
                ->onlyOnDetail()
                ->monospaced()
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return true;
                    }

                    return $request->user()->can('read-users-api_token');
                }),

            Text::make('Phone Number', 'phone')
                ->hideFromIndex()
                ->rules('nullable', 'max:15'),

            Boolean::make('Active', 'is_active')
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Boolean::make('Latest Agreement Signed', function (): bool {
                return $this->hasSignedLatestAgreement();
            })->onlyOnDetail(),

            HasMany::make('Signatures'),

            new Panel(
                'System Access',
                [
                    Boolean::make('Active', 'is_access_active')
                        ->onlyOnDetail(),

                    DateTime::make('Override Expiration', 'access_override_until')
                        ->onlyOnDetail(),

                    BelongsTo::make('Override Entered By', 'accessOverrideBy', self::class)
                        ->onlyOnDetail(),
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

                    Text::make('Autodesk', 'autodesk_email')
                        ->hideFromIndex()
                        ->rules('nullable', 'max:255', 'email')
                        ->creationRules('unique:users,autodesk_email')
                        ->updateRules('unique:users,autodesk_email,{{resourceId}}'),

                    Boolean::make('Autodesk Invite Pending', 'autodesk_invite_pending')
                        ->hideFromIndex()
                        ->help('This flag is set by JEDI but may be out of sync with Autodesk in some cases.'
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
                    File::make('Resume', function (): ?string {
                        return null !== $this->resume_date ? 'resumes/'.$this->uid.'.pdf' : null;
                    })->path('resumes')
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
                ->readonly(static function (Request $request): bool {
                    return ! $request->user()->hasRole('admin');
                }),

            BelongsToMany::make('Class Standing', 'classStanding')
                ->readonly(static function (Request $request): bool {
                    return ! $request->user()->hasRole('admin');
                }),

            HasMany::make('Recruiting Visits', 'recruitingVisits')
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-recruiting-visits-own');
                    }

                    return $request->user()->can('read-recruiting-visits');
                }),

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
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-emergency_contact');
                }),

            Text::make('Emergency Contact Phone Number', 'emergency_contact_phone')
                ->hideFromIndex()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-emergency_contact');
                }),
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
                ->required(),

            Text::make('gtDirGUID')
                ->hideFromIndex(),

            MorphToMany::make('Roles', 'roles', \Vyuldashev\NovaPermission\Role::class)
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                }),

            MorphToMany::make('Permissions', 'permissions', \Vyuldashev\NovaPermission\Permission::class)
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                }),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<\Laravel\Nova\Card>
     */
    public function cards(Request $request): array
    {
        return [
            (new MemberSince())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-payments');
                }),
            (new TotalAttendance())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),
            (new PrimaryTeam())
                ->onlyOnDetail()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-attendance');
                }),
            (new ResumesSubmitted())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-resume');
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
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
    public function actions(Request $request): array
    {
        return [
            (new Actions\SyncAccess())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })
                ->canRun(static function (Request $request, AppModelsUser $user): bool {
                    return $request->user()->hasRole('admin');
                }),
            (new Actions\OverrideAccess())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })
                ->canRun(static function (Request $request, AppModelsUser $user): bool {
                    return $request->user()->hasRole('admin');
                }),
            (new Actions\ResetApiToken())
                ->canSee(static function (Request $request): bool {
                    return true;
                })
                ->canRun(static function (Request $request, AppModelsUser $user): bool {
                    return $request->user()->hasRole('admin') || ($request->user()->id === $user->id);
                }),
            (new Actions\SendNotification())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('send-notifications');
                })
                ->canRun(static function (Request $request, AppModelsUser $user): bool {
                    return $request->user()->can('send-notifications');
                }),
            (new Actions\GenerateResumeBook())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-resume');
                })->standalone(),
            (new Actions\RefreshFromGTED())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })
                ->canRun(static function (Request $request, AppModelsUser $user): bool {
                    return $request->user()->hasRole('admin');
                }),
            (new Actions\ExportUsersBuzzCardAccess())
                ->standalone()
                ->onlyOnIndex()
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users');
                })
                ->canRun(static function (Request $request, AppModelsUser $user): bool {
                    return $request->user()->can('read-users');
                }),
        ];
    }
}
