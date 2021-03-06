<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Providers;

use App\Models\User;
use App\Nova\Cards\MakeAWish;
use App\Nova\Dashboards\Demographics;
use App\Nova\Dashboards\JEDI;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\DuesRevenueByFiscalYear;
use App\Nova\Metrics\MembersByFiscalYear;
use App\Nova\Metrics\PaymentsPerDay;
use App\Nova\Tools\AttendanceReport;
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
        return [
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
            new MakeAWish(),
        ];
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
