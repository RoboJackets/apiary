<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
// phpcs:disable SlevomatCodingStandard.Functions.DisallowNamedArguments.DisallowedNamedArgument
// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

namespace App\Providers;

use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\OAuth2AccessToken;
use App\Models\OAuth2Client;
use App\Models\Signature;
use App\Models\Team;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Policies\NotificationPolicy;
use App\Policies\WebhookCallPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\MasterSupervisor;
use Laravel\Nova\Notifications\Notification;
use Laravel\Passport\Passport;
use Spatie\WebhookClient\Models\WebhookCall;

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

        Relation::morphMap([
            'dues-transaction' => DuesTransaction::class,
            'event' => Event::class,
            'signature' => Signature::class,
            'team' => Team::class,
            'travel-assignment' => TravelAssignment::class,
        ]);

        $this->bootAuth();

        Gate::policy(WebhookCall::class, WebhookCallPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);

        Auth::provider(
            'user_or_client',
            static fn ($app, array $config): UserOrClientUserProvider => new UserOrClientUserProvider()
        );
    }

    public function bootAuth(): void
    {
        Passport::useClientModel(OAuth2Client::class);
        Passport::useTokenModel(OAuth2AccessToken::class);
        Passport::tokensExpireIn(now()->addDay());
        Passport::refreshTokensExpireIn(now()->addMonth());
        Passport::personalAccessTokensExpireIn(now()->addYear());
        Passport::cookie(config('passport.cookie_name'));
        Passport::$deviceCodeGrantEnabled = false;
        Passport::authorizationView('errors.generic');
    }
}
