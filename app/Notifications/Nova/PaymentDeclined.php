<?php

declare(strict_types=1);

namespace App\Notifications\Nova;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL;
use Spatie\WebhookClient\Models\WebhookCall;

class PaymentDeclined extends NovaNotification implements ShouldQueue
{
    use Queueable;

    /**
     * The icon used for the notification.
     *
     * @var string
     */
    public $icon = 'exclamation';

    /**
     * The text used for the call-to-action button label.
     *
     * @var string
     */
    public $actionText = 'View webhook call';

    /**
     * The notification's visual type.
     *
     * @var string
     */
    public $type = 'error';

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment, WebhookCall $webhookCall)
    {
        $this->message = 'A payment from '.$payment->payable->user->full_name.' was declined';
        $this->actionUrl = URL::remote(route(
            'nova.pages.detail',
            [
                'resource' => \App\Nova\WebhookCall::uriKey(),
                'resourceId' => $webhookCall->id,
            ]
        ));
    }
}
