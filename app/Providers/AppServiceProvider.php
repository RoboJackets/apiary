<?php

declare(strict_types=1);

// @phan-file-suppress PhanPluginAlwaysReturnFunction
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

namespace App\Providers;

use App\Models\Attendance;
use App\Models\DocuSignEnvelope;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\MembershipAgreementTemplate;
use App\Models\Payment;
use App\Models\Signature;
use App\Models\Team;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Observers\AttendanceObserver;
use App\Observers\DocuSignEnvelopeObserver;
use App\Observers\DuesPackageObserver;
use App\Observers\DuesTransactionObserver;
use App\Observers\MembershipAgreementTemplateObserver;
use App\Observers\PaymentObserver;
use App\Observers\TravelAssignmentObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Horizon::auth(static function (): bool {
            if (auth()->guard('web')->user() instanceof User && auth()->guard('web')->user()->can('access-horizon')) {
                return true;
            }

            if (auth()->guard('web')->user() === null) {
                // Theoretically, this should never happen since we're calling the CAS middleware before this.
                abort(401, 'Authentication Required');
            }

            abort(403, 'Forbidden');

            // No return as this is unreachable.
        });

        if (config('horizon.master_supervisor_name') !== null) {
            MasterSupervisor::determineNameUsing(static fn (): string => config('horizon.master_supervisor_name'));
        }

        Model::shouldBeStrict();

        // Lazy-loading needs to be allowed for console commands due to https://github.com/laravel/scout/issues/462
        if ($this->app->runningInConsole()) {
            Model::preventLazyLoading(false);
        }

        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(static function (Model $model, string $relation): void {
                \Sentry\captureMessage('Attempted to lazy-load '.$relation.' on '.$model::class);
            });
        }

        Attendance::observe(AttendanceObserver::class);
        DocuSignEnvelope::observe(DocuSignEnvelopeObserver::class);
        DuesPackage::observe(DuesPackageObserver::class);
        DuesTransaction::observe(DuesTransactionObserver::class);
        MembershipAgreementTemplate::observe(MembershipAgreementTemplateObserver::class);
        Payment::observe(PaymentObserver::class);
        TravelAssignment::observe(TravelAssignmentObserver::class);
        User::observe(UserObserver::class);

        Relation::morphMap([
            'dues-transaction' => DuesTransaction::class,
            'event' => Event::class,
            'signature' => Signature::class,
            'team' => Team::class,
            'travel-assignment' => TravelAssignment::class,
        ]);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreMigrations();
        Passport::$passwordGrantEnabled = false;
    }
}
