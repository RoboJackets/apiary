<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Providers;

use App\Nova\Cards\MakeAWish;
use App\Nova\Dashboards\Jedi;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use App\Nova\Metrics\ActiveMembers;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\PaymentsPerDay;
use App\Nova\Tools\AttendanceReport;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function boot(): void
    {
        parent::boot();
        Nova::serving(static function (ServingNova $event): void {
            Nova::script('apiary-custom', __DIR__.'/../../public/js/nova.js');
            Nova::style('apiary-custom', __DIR__.'/../../public/css/nova.css');
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes(): void
    {
        Nova::routes()->withAuthenticationRoutes()->withPasswordResetRoutes()->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
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
            new PaymentsPerDay(),
            new ActiveMembers(),
            new AttendancePerWeek(),
            new ActiveAttendanceBreakdown(),
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
            (new \Vyuldashev\NovaPermission\NovaPermissionTool())->canSee(static function (Request $request): bool {
                return $request->user()->hasRole('admin');
            }),
            (new AttendanceReport())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-attendance');
            }),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            (new Jedi())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
        ];
    }
}
