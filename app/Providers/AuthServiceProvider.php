<?php

declare(strict_types=1);

namespace App\Providers;

use App\Attendance;
use App\AttendanceExport;
use App\DuesPackage;
use App\DuesTransaction;
use App\Event;
use App\Major;
use App\NotificationTemplate;
use App\Payment;
use App\Policies\AttendanceExportPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\DuesPackagePolicy;
use App\Policies\DuesTransactionPolicy;
use App\Policies\EventPolicy;
use App\Policies\MajorPolicy;
use App\Policies\NotificationTemplatePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RecruitingVisitPolicy;
use App\Policies\RemoteAttendanceLinkPolicy;
use App\Policies\RolePolicy;
use App\Policies\RsvpPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\RecruitingVisit;
use App\RemoteAttendanceLink;
use App\Rsvp;
use App\Team;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<string,string>
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
        RemoteAttendanceLink::class => RemoteAttendanceLinkPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
