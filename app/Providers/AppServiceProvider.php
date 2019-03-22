<?php

namespace App\Providers;

use App\Team;
use App\User;
use App\Payment;
use App\DuesPackage;
use Laravel\Horizon\Horizon;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\Facades\Auth;
use App\Observers\DuesPackageObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\Resource;

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

        Horizon::auth(function () {
            if (auth()->guard('web')->user() instanceof User &&
                auth()->guard('web')->user()->can('access-horizon')) {
                return true;
            } elseif (auth()->guard('web')->user() == null) {
                // Theoretically, this should never happen since we're calling the CAS middleware before this.
                return abort(401, 'Authentication Required');
            } else {
                return abort(403, 'Forbidden');
            }
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
