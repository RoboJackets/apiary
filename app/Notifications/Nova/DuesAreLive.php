<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class DuesAreLive extends NovaNotification implements ShouldQueue, ShouldBeUnique
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
    public $message = 'Dues are live! Take a moment to pay online now.';

    /**
     * The text used for the call-to-action button label.
     *
     * @var string
     */
    public $actionText = 'Get started';

    /**
     * The notification's visual type.
     *
     * @var string
     */
    public $type = 'info';

    private int $userId;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->actionUrl = URL::remote(route('showDuesFlow'));
        $this->userId = $user->id;
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return strval($this->userId);
    }
}
