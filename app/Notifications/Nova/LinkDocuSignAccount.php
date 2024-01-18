<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;

/**
 * This notification is sent when someone needs to link their DocuSign account.
 *
 * It is automatically deleted by \App\Util\DocuSign when they finish the flow.
 */
class LinkDocuSignAccount extends NovaNotification
{
    /**
     * The icon used for the notification.
     *
     * @var string
     */
    public $icon = 'exclamation';

    /**
     * The message used for the notification.
     *
     * @var string|null
     */
    public $message = 'Link your DocuSign account to send forms for ';

    /**
     * The text used for the call-to-action button label.
     *
     * @var string
     */
    public $actionText = 'Link DocuSign Account';

    /**
     * Determine if URL should be open in new tab.
     *
     * @var bool
     */
    public $openInNewTab = true;

    /**
     * The notification's visual type.
     *
     * @var string
     */
    public $type = 'warning';

    /**
     * Create a new notification instance.
     */
    public function __construct(string $tripName)
    {
        $this->message .= $tripName;
        $this->actionUrl = URL::remote(route('docusign.auth.user'));
    }
}
