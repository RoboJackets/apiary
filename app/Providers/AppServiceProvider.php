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
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        User::observe(UserObserver::class);
        Payment::observe(PaymentObserver::class);
        Attendance::observe(AttendanceObserver::class);
        DuesPackage::observe(DuesPackageObserver::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
