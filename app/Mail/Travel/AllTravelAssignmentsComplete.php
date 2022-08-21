<?php

declare(strict_types=1);

namespace App\Mail\Travel;

use App\Models\Travel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class AllTravelAssignmentsComplete extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Travel $travel)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->travel->primaryContact->gt_email, $this->travel->primaryContact->name)
                    ->cc(config('services.treasurer_email'))
                    ->subject('All travel assignments completed for '.$this->travel->name)
                    ->text('mail.travel.allassignmentscomplete')
                    ->withSymfonyMessage(static function (Email $email): void {
                        $email->replyTo(config('services.treasurer_email'));
                    })
                    ->tag('travel-assignments-complete')
                    ->metadata('travel-id', strval($this->travel->id));
    }
}
