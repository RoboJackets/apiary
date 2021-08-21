<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Providers;

use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Nova\Cards\MakeAWish;
use App\Nova\Dashboards\Demographics;
use App\Nova\Dashboards\JEDI;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\DocumentsReceivedForTravel;
use App\Nova\Metrics\DuesRevenueByFiscalYear;
use App\Nova\Metrics\MembersByFiscalYear;
use App\Nova\Metrics\PaymentReceivedForTravel;
use App\Nova\Metrics\PaymentsPerDay;
use App\Nova\Metrics\TransactionsByDuesPackage;
use App\Nova\Tools\AttendanceReport;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Vyuldashev\NovaPermission\NovaPermissionTool;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
        Nova::serving(static function (ServingNova $event): void {
            Nova::script('apiary-custom', asset('js/nova.js'));
            Nova::theme(asset('css/nova.css'));
        });
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()->withAuthenticationRoutes()->withPasswordResetRoutes()->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewNova', static function (User $user): bool {
            return $user->can('access-nova');
        });
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array<\Laravel\Nova\Card>
     */
    protected function cards(): array
    {
        $cards = [
            (new PaymentsPerDay())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-payments');
            }),
            (new MembersByFiscalYear())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-dues-transactions');
            }),
            (new DuesRevenueByFiscalYear())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-dues-transactions');
            }),
            (new AttendancePerWeek())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-attendance');
            }),
            (new ActiveAttendanceBreakdown())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users') && $request->user()->can('read-attendance');
            }),
            (new TransactionsByDuesPackage())
                ->canSee(static function (Request $request): bool {
                    return $request->user()->can('read-payments');
                }),
        ];

        foreach (Travel::all() as $travel) {
            $should_include = false;

            if ($travel->return_date > Carbon::now()) {
                $should_include = true;
            }

            if (
                null !== $travel->documents_required
                && $travel->assignments()->where('documents_received', false)->exists()
            ) {
                $should_include = true;
            }

            if (
                $travel->assignments()->leftJoin('payments', static function (JoinClause $join): void {
                    $join->on('travel_assignments.id', '=', 'payable_id')
                         ->where('payments.amount', '>', 0)
                         ->where('payments.payable_type', TravelAssignment::getMorphClassStatic())
                         ->whereNull('payments.deleted_at');
                })->whereNull('payments.id')->exists()
            ) {
                $should_include = true;
            }

            if (! $should_include) {
                continue;
            }

            if (null !== $travel->documents_required) {
                $cards[] = new DocumentsReceivedForTravel($travel->id);
            }

            $cards[] = (new PaymentReceivedForTravel($travel->id))->canSee(static function (Request $request): bool {
                return $request->user()->can('read-payments');
            });
        }

        $cards[] = new MakeAWish();

        return $cards;
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<\Laravel\Nova\Tool>
     */
    public function tools(): array
    {
        return [
            (new NovaPermissionTool())->canSee(static function (Request $request): bool {
                return $request->user()->hasRole('admin');
            }),
            (new AttendanceReport())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-attendance');
            }),
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array<\Laravel\Nova\Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            (new JEDI())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new Demographics())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
        ];
    }
}
