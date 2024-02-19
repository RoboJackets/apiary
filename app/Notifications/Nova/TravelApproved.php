<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use App\Models\Travel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class TravelApproved extends NovaNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The icon used for the notification.
     *
     * @var string
     */
    public $icon = 'globe';

    /**
     * The text used for the call-to-action button label.
     *
     * @var string
     */
    public $actionText = 'View trip';

    /**
     * The notification's visual type.
     *
     * @var string
     */
    public $type = 'info';

    /**
     * Create a new notification instance.
     */
    public function __construct(Travel $trip)
    {
        $this->message = $trip->name.' was approved!';
        $this->actionUrl = URL::remote(route(
            'nova.pages.detail',
            [
                'resource' => \App\Nova\Travel::uriKey(),
                'resourceId' => $trip->id,
            ]
        ));
    }
}
