<?php

declare(strict_types=1);

namespace App\Providers;

use App\Rsvp;
use App\Team;
use App\User;
use App\Event;
use App\Payment;
use App\Attendance;
use App\DuesPackage;
use App\DuesTransaction;
use App\RecruitingVisit;
use App\Policies\RsvpPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use App\NotificationTemplate;
use App\Policies\EventPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\DuesPackagePolicy;
use App\Policies\DuesTransactionPolicy;
use App\Policies\RecruitingVisitPolicy;
use App\Policies\NotificationTemplatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        DuesPackage::class => DuesPackagePolicy::class,
        NotificationTemplate::class => NotificationTemplatePolicy::class,
        RecruitingVisit::class => RecruitingVisitPolicy::class,
        Payment::class => PaymentPolicy::class,
        DuesTransaction::class => DuesTransactionPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
