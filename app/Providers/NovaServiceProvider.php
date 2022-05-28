<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Providers;

use App\Models\User;
use App\Nova\Dashboards\Demographics;
use App\Nova\Dashboards\JEDI;
use App\Nova\Dashboards\Main;
use App\Nova\Tools\AttendanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
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
        });

        Nova::footer(static function (Request $request): string {
            return '
<p class="mt-8 text-center text-xs text-80">
    <a class="text-primary dim no-underline" href="https://github.com/RoboJackets/apiary">Made with ♥ by RoboJackets</a>
    <span class="px-1">&middot;</span>
    <a class="text-primary dim no-underline" class="text-muted" href="/privacy">Privacy Policy</a>
</p>
';
        });

        Nova::report(static function (\Throwable $exception): void {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($exception);
            }
        });
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewNova', static function (User $user): bool {
            return Cache::remember(
                'can_access_nova_'.$user->uid,
                now()->addDay(),
                static function () use ($user): bool {
                    return $user->can('access-nova');
                }
            );
        });
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<\Laravel\Nova\Tool>
     */
    public function tools(): array
    {
        return [
            // (new AttendanceReport())->canSee(static function (Request $request): bool {
            //     return $request->user()->can('read-attendance');
            // }),
            (new NovaPermissionTool())->canSee(static function (Request $request): bool {
                return $request->user()->hasRole('admin');
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
            new Main(),
            (new JEDI())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
            (new Demographics())->canSee(static function (Request $request): bool {
                return $request->user()->can('read-users');
            }),
        ];
    }
}
