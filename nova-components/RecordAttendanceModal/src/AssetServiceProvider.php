<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter

namespace RoboJackets\RecordAttendanceModal;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

/**
 * Registers the modal with Nova.
 *
 * @phan-suppress PhanRedefineClass
 */
class AssetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Nova::serving(static function (ServingNova $event): void {
            Nova::script('record-attendance', __DIR__.'/../dist/js/asset.js');
        });
    }
}
