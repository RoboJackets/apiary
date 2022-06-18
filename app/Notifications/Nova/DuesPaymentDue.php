<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

/**
 * This notification is sent when someone needs to complete a payment for an existing DuesTransaction.
 *
 * It is automatically deleted by \App\Jobs\PruneDuesNotificationsInNova when the user becomes active.
 */
class DuesPaymentDue extends NovaNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The icon used for the notification.
     *
     * @var string
     */
    public $icon = 'currency-dollar';

    /**
     * The message used for the notification.
     *
     * @var string|null
     */
    public $message = 'You still need to pay dues!';

    /**
     * The text used for the call-to-action button label.
     *
     * @var string
     */
    public $actionText = 'Pay online now';

    /**
     * The notification's visual type.
     *
     * @var string
     */
    public $type = 'info';

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->actionUrl = URL::remote(route('pay.dues'));
    }
}
