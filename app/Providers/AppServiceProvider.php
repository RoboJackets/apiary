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
use App\Sentry\Helpers;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Passport\Passport;
use Sentry\EventHint;
use Symfony\Component\HttpFoundation\Response;

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

        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(static function (Model $model, string $relation): void {
                \Sentry\captureMessage('Attemped to lazy-load '.$relation.' on '.$model::class);
            });
        }

        if (! $this->app->runningInConsole()) {
            DB::whenQueryingForLongerThan(100, static function (Connection $connection): void {
                \Sentry\captureMessage('Total database query time exceeded 100ms');
            });

            DB::listen(static function (QueryExecuted $query): void {
                if ($query->time > 100) {
                    \Sentry\captureMessage(
                        message: 'Database query took '.$query->time.'ms',
                        hint: EventHint::fromArray(['extra' => ['query' => $query->sql]])
                    );
                }
            });
        }

        // @phan-suppress-next-line PhanTypeArraySuspicious
        $this->app[Kernel::class]->whenRequestLifecycleIsLongerThan(
            100,
            static function (Carbon $startedAt, Request $request, Response $response): void {
                if (! Helpers::shouldIgnoreUrl($request->path()) && ! $request->is('pay/*')) {
                    \Sentry\captureMessage(
                        $request->method().' '.$request->path().' took '
                        .$startedAt->diffAsCarbonInterval()->milliseconds.'ms'
                    );
                }
            }
        );

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
    }
}
