<?php

namespace App\Providers;

use App\Rsvp;
use App\Attendance;
use App\Policies\RsvpPolicy;
use App\Policies\AttendancePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Attendance::class => AttendancePolicy::class,
        Rsvp::class => RsvpPolicy::class,
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
