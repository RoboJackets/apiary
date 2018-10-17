<?php

namespace App\Providers;

use Laravel\Nova\Nova;
use App\Nova\Metrics\ActiveMembers;
use App\Nova\Metrics\PaymentsPerDay;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use App\Nova\Metrics\AttendancePerWeek;
use App\Nova\Metrics\ActiveAttendanceBreakdown;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Nova::serving(function (ServingNova $event) {
            Nova::style('apiary-custom', __DIR__.'/../../resources/assets/css/nova.css');
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return $user->hasRole('admin');
        });
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new PaymentsPerDay,
            new ActiveMembers,
            new AttendancePerWeek,
            new ActiveAttendanceBreakdown,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            new \Vyuldashev\NovaPermission\NovaPermissionTool(),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
