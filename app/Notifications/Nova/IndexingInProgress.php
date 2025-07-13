<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

class IndexingInProgress extends NovaNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The icon used for the notification.
     *
     * @var string
     */
    public $icon = 'magnifying-glass';

    /**
     * The message used for the notification.
     *
     * @var string|null
     */
    public $message = 'Search indexing is in progress. Search results may be incomplete.';

    /**
     * The text used for the call-to-action button label.
     *
     * @var string
     */
    public $actionText = 'View indexing status';

    /**
     * The notification's visual type.
     *
     * @var string
     */
    public $type = 'warning';

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        $this->actionUrl = URL::remote(route('horizon.index'));
    }
}
