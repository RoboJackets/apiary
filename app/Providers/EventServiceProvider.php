<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string,array<string>>
     */
    protected $listen = [
        \App\Events\PaymentSuccess::class => [
            \App\Listeners\PaymentSuccessListener::class,
        ],
    ];
}
