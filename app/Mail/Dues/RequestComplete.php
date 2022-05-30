<?php

declare(strict_types=1);

namespace App\Mail\Dues;

use App\Models\DuesPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class RequestComplete extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The DuesPackage selected.
     *
     * @var DuesPackage
     */
    public $duesPackage;

    /**
     * Create a new message instance.
     */
    public function __construct(DuesPackage $duesPackage)
    {
        $this->duesPackage = $duesPackage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('noreply@my.robojackets.org', 'RoboJackets')
            ->withSymfonyMessage(static function (Email $message): void {
                $message->replyTo('RoboJackets <treasurer@robojackets.org>');
            })->subject('[RoboJackets] ACTION REQUIRED | Dues Form Received')
            ->markdown('mail.dues.requestcomplete');
    }
}
