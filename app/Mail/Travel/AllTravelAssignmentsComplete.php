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

    public Travel $travel;

    /**
     * Create a new message instance.
     */
    public function __construct(Travel $travel)
    {
        $this->travel = $travel;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->travel->primaryContact->gt_email, $this->travel->primaryContact->name)
                    ->subject('All travel assignments completed for '.$this->travel->name)
                    ->text('mail.travel.allassignmentscomplete')
                    ->withSymfonyMessage(static function (Email $email): void {
                        $email->replyTo('RoboJackets <treasurer@robojackets.org>');
                    })
                    ->tag('travel-assignments-complete')
                    ->metadata('travel-id', strval($this->travel->id));
    }
}
