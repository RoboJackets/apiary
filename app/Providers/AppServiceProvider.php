<?php

declare(strict_types=1);

// @phan-file-suppress PhanPluginAlwaysReturnFunction

namespace App\Providers;

use App\Models\Attendance;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\MembershipAgreementTemplate;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\Team;
use App\Models\User;
use App\Observers\AttendanceObserver;
use App\Observers\DuesPackageObserver;
use App\Observers\MembershipAgreementTemplateObserver;
use App\Observers\PaymentObserver;
use App\Observers\SignatureObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Horizon::auth(static function (): bool {
            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            if (auth()->guard('web')->user() instanceof User
                // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
                && auth()->guard('web')->user()->can('access-horizon')
            ) {
                return true;
            }

            // @phan-suppress-next-line PhanPossiblyUndeclaredMethod
            if (null === auth()->guard('web')->user()) {
                // Theoretically, this should never happen since we're calling the CAS middleware before this.
                abort(401, 'Authentication Required');
            }

            abort(403, 'Forbidden');

            // No return as this is unreachable.
        });

        Attendance::observe(AttendanceObserver::class);
        DuesPackage::observe(DuesPackageObserver::class);
        MembershipAgreementTemplate::observe(MembershipAgreementTemplateObserver::class);
        Payment::observe(PaymentObserver::class);
        Signature::observe(SignatureObserver::class);
        User::observe(UserObserver::class);

        Relation::morphMap([
            'event' => Event::class,
            'dues-transaction' => DuesTransaction::class,
            'team' => Team::class,
        ]);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
