<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Attendance;
use App\Models\AttendanceExport;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\Event;
use App\Models\Major;
use App\Models\Merchandise;
use App\Models\NotificationTemplate;
use App\Models\OAuth2AccessToken;
use App\Models\OAuth2Client;
use App\Models\Payment;
use App\Models\RecruitingVisit;
use App\Models\RemoteAttendanceLink;
use App\Models\Rsvp;
use App\Models\Team;
use App\Models\Travel;
use App\Models\TravelAssignment;
use App\Models\User;
use App\Policies\AttendanceExportPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\DuesPackagePolicy;
use App\Policies\DuesTransactionPolicy;
use App\Policies\EventPolicy;
use App\Policies\MajorPolicy;
use App\Policies\MerchandisePolicy;
use App\Policies\NotificationTemplatePolicy;
use App\Policies\OAuth2AccessTokenPolicy;
use App\Policies\OAuth2ClientPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RecruitingVisitPolicy;
use App\Policies\RemoteAttendanceLinkPolicy;
use App\Policies\RolePolicy;
use App\Policies\RsvpPolicy;
use App\Policies\TeamPolicy;
use App\Policies\TravelAssignmentPolicy;
use App\Policies\TravelPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string,class-string>
     */
    protected $policies = [
        Rsvp::class => RsvpPolicy::class,
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        Event::class => EventPolicy::class,
        Attendance::class => AttendancePolicy::class,
        AttendanceExport::class => AttendanceExportPolicy::class,
        DuesPackage::class => DuesPackagePolicy::class,
        NotificationTemplate::class => NotificationTemplatePolicy::class,
        RecruitingVisit::class => RecruitingVisitPolicy::class,
        Payment::class => PaymentPolicy::class,
        DuesTransaction::class => DuesTransactionPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        Major::class => MajorPolicy::class,
        Merchandise::class => MerchandisePolicy::class,
        RemoteAttendanceLink::class => RemoteAttendanceLinkPolicy::class,
        Travel::class => TravelPolicy::class,
        TravelAssignment::class => TravelAssignmentPolicy::class,
        OAuth2Client::class => OAuth2ClientPolicy::class,
        OAuth2AccessToken::class => OAuth2AccessTokenPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        if (! $this->app->routesAreCached()) {
            Passport::routes();
        }

        Passport::useClientModel(OAuth2Client::class);
        Passport::useTokenModel(OAuth2AccessToken::class);
        Passport::hashClientSecrets();
        Passport::tokensExpireIn(now()->addDay());
        Passport::refreshTokensExpireIn(now()->addMonth());
        Passport::personalAccessTokensExpireIn(now()->addYear());
        Passport::cookie('__Host-apiary_token');
    }
}
