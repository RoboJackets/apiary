<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class DuesPaymentDue extends NovaNotification implements ShouldQueue, ShouldBeUnique
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

    private int $userId;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->actionUrl = URL::remote(route('pay.dues'));
        $this->userId = $user->id;
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return strval($this->userId);
    }
}
