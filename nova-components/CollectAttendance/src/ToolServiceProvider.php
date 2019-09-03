<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace Robojackets\Apiary\Nova\CollectAttendance;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $this->routes();
        });

        Nova::serving(static function (ServingNova $event): void {
            Nova::script('nova-collect-attendance', __DIR__.'/../dist/js/tool.js');
            Nova::style('nova-collect-attendance', __DIR__.'/../dist/css/tool.css');
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes(): void
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
    }
}
