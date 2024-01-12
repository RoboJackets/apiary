<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Airport;
use App\Models\Attendance;
use App\Models\DocuSignEnvelope;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\Major;
use App\Models\Merchandise;
use App\Models\OAuth2AccessToken;
use App\Models\OAuth2Client;
use App\Models\Payment;
use App\Models\RemoteAttendanceLink;
use App\Models\Rsvp;
use App\Models\Team;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Policies\AirportPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\DocuSignEnvelopePolicy;
use App\Policies\DuesPackagePolicy;
use App\Policies\DuesTransactionPolicy;
use App\Policies\EventPolicy;
use App\Policies\MajorPolicy;
use App\Policies\MerchandisePolicy;
use App\Policies\NovaNotificationPolicy;
use App\Policies\OAuth2AccessTokenPolicy;
use App\Policies\OAuth2ClientPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RemoteAttendanceLinkPolicy;
use App\Policies\RolePolicy;
use App\Policies\RsvpPolicy;
use App\Policies\TeamPolicy;
use App\Policies\TravelAssignmentPolicy;
use App\Policies\TravelPolicy;
use App\Policies\UserPolicy;
use App\Policies\WebhookCallPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Nova\Notifications\Notification;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\WebhookClient\Models\WebhookCall;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string,class-string>
     */
    protected $policies = [
        Airport::class => AirportPolicy::class,
        Attendance::class => AttendancePolicy::class,
        DocuSignEnvelope::class => DocuSignEnvelopePolicy::class,
        DuesPackage::class => DuesPackagePolicy::class,
        DuesTransaction::class => DuesTransactionPolicy::class,
        Event::class => EventPolicy::class,
        Major::class => MajorPolicy::class,
        Merchandise::class => MerchandisePolicy::class,
        Notification::class => NovaNotificationPolicy::class,
        OAuth2AccessToken::class => OAuth2AccessTokenPolicy::class,
        OAuth2Client::class => OAuth2ClientPolicy::class,
        Payment::class => PaymentPolicy::class,
        Permission::class => PermissionPolicy::class,
        RemoteAttendanceLink::class => RemoteAttendanceLinkPolicy::class,
        Role::class => RolePolicy::class,
        Rsvp::class => RsvpPolicy::class,
        Team::class => TeamPolicy::class,
        Travel::class => TravelPolicy::class,
        TravelAssignment::class => TravelAssignmentPolicy::class,
        User::class => UserPolicy::class,
        WebhookCall::class => WebhookCallPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::useClientModel(OAuth2Client::class);
        Passport::useTokenModel(OAuth2AccessToken::class);
        Passport::hashClientSecrets();
        Passport::tokensExpireIn(now()->addDay());
        Passport::refreshTokensExpireIn(now()->addMonth());
        Passport::personalAccessTokensExpireIn(now()->addYear());
        Passport::cookie(config('passport.cookie_name'));
    }
}
