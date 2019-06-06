<?php

namespace App\Mail;

use App\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Swift_Mime_SimpleMimeEntity as SimpleMimeEntity;

class DatabaseMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $app_url;

    public $template_id;

    public $metadata;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template_id, $metadata)
    {
        $this->app_url = url('/');
        $this->template_id = $template_id;
        $this->metadata = $metadata;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nt = NotificationTemplate::find($this->template_id);
        if (! $nt) {
            die('Could not find template');
        }

        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
            ->withSwiftMessage(static function (SimpleMimeEntity $message): void {
                $message->getHeaders()
                    ->addTextHeader('Reply-To', 'RoboJackets <info@robojackets.org>');
            })
            ->subject($nt->subject)
            ->markdown('mail.database', ['markdown' => $nt->body_markdown, 'metadata' => $this->metadata]);
    }
}
