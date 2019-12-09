<?php

declare(strict_types=1);

namespace App\Providers;

use App\Attendance;
use App\DuesPackage;
use App\Observers\AttendanceObserver;
use App\Observers\DuesPackageObserver;
use App\Observers\PaymentObserver;
use App\Observers\UserObserver;
use App\Payment;
use App\User;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Resource::withoutWrapping();

        Horizon::auth(static function () {
            if (
                auth()->guard('web')->user() instanceof User
                && auth()->guard('web')->user()->can('access-horizon')
            ) {
                return true;
            }

            if (null === auth()->guard('web')->user()) {
                // Theoretically, this should never happen since we're calling the CAS middleware before this.
                abort(401, 'Authentication Required');
            }

            abort(403, 'Forbidden');
        });

        User::observe(UserObserver::class);
        Payment::observe(PaymentObserver::class);
        Attendance::observe(AttendanceObserver::class);
        DuesPackage::observe(DuesPackageObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
