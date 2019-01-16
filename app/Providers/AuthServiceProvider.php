<?php

namespace App\Providers;

use App\Rsvp;
use App\Team;
use App\User;
use App\Event;
use App\Attendance;
use App\DuesPackage;
use App\RecruitingVisit;
use App\Policies\RecruitingVisitPolicy;
use App\Policies\RsvpPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\NotificationTemplate;
use App\Policies\EventPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\DuesPackagePolicy;
use App\Policies\NotificationTemplatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Rsvp::class => RsvpPolicy::class,
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
        Event::class => EventPolicy::class,
        Attendance::class => AttendancePolicy::class,
        DuesPackage::class => DuesPackagePolicy::class,
        NotificationTemplate::class => NotificationTemplatePolicy::class,
        RecruitingVisit::class => RecruitingVisitPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
