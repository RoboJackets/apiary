<?php

namespace App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\Resource;
use App\User;
use App\Payment;
use App\DuesPackage;
use App\Observers\UserObserver;
use App\Observers\PaymentObserver;
use App\Observers\DuesPackageObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Resource::withoutWrapping();

        Horizon::auth(function ($request) {
            return auth()->user()->can('access-horizon');
        });

        User::observe(UserObserver::class);
        Payment::observe(PaymentObserver::class);
        DuesPackage::observe(DuesPackageObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
