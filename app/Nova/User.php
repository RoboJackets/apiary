<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint

namespace App\Nova;

use App\User as AU;
use Laravel\Nova\Panel;
use App\Nova\Fields\Hidden;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use App\Nova\Metrics\MemberSince;
use App\Nova\Metrics\PrimaryTeam;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\MorphToMany;
use App\Nova\Metrics\TotalAttendance;
use Laravel\Nova\Fields\BelongsToMany;

class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

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
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<mixed>
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
                ->sortable()
                ->rules('nullable', 'max:127'),

            Text::make('Legal First Name', 'first_name')
                ->sortable()
                ->rules('required', 'max:127'),

            Text::make('Last Name')
                ->sortable()
                ->rules('required', 'max:127'),

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

            Text::make('GitHub Username', 'github_username')
                ->hideFromIndex()
                ->rules('nullable', 'max:40'),

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

            new Panel('Emergency Contact', $this->emergencyFields()),

            new Panel('Swag', $this->swagFields()),

            HasMany::make('Recruiting Visits', 'recruitingVisits')
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-recruiting-visits-own');
                    }

                    return $request->user()->can('read-recruiting-visits');
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

            HasMany::make('Dues Transactions', 'duesTransactions', DuesTransaction::class)
                ->canSee(static function (Request $request): bool {
                    if ($request->resourceId === $request->user()->id) {
                        return $request->user()->can('read-dues-transactions-own');
                    }

                    return $request->user()->can('read-dues-transactions');
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
        $shirt_sizes = [
            's' => 'Small',
            'm' => 'Medium',
            'l' => 'Large',
            'xl' => 'Extra-Large',
            'xxl' => 'XXL',
            'xxxl' => 'XXXL',
        ];

        return [
            Select::make('T-Shirt Size', 'shirt_size')
                ->options($shirt_sizes)
                ->displayUsingLabels()
                ->hideFromIndex(),

            Select::make('Polo Size')
                ->options($shirt_sizes)
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
     * @param \Illuminate\Http\Request  $request
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
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Filters\Filter>
     */
    public function filters(Request $request): array
    {
        return [
            new Filters\UserActive(),
            new Filters\UserType(),
            new Filters\UserTeam(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
     *
     * @return array<\Laravel\Nova\Lenses\Lens>
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request  $request
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
                ->canRun(static function (Request $request, AU $user): bool {
                    return $request->user()->hasRole('admin');
                }),
            (new Actions\OverrideAccess())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->hasRole('admin');
                })
                ->canRun(static function (Request $request, AU $user): bool {
                    return $request->user()->hasRole('admin');
                }),
            (new Actions\ResetApiToken())
                ->canSee(static function (Request $request): bool {
                    return true;
                })
                ->canRun(static function (Request $request, AU $user): bool {
                    return $request->user()->hasRole('admin') || ($request->user()->id === $user->id);
                }),
            (new Actions\ExportGtid())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-users-gtid');
                }),
            (new Actions\ExportUsername())
                ->canRun(static function (Request $request, AU $user): bool {
                    return $request->user()->can('read-users');
                }),
        ];
    }
}
