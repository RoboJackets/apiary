<?php

declare(strict_types=1);

namespace App\Mail\Travel;

use App\Models\TravelAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class TravelAssignmentCreated extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public TravelAssignment $assignment)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->from('noreply@my.robojackets.org', 'RoboJackets')
                    ->to($this->assignment->user->gt_email, $this->assignment->user->name)
                    ->subject(
                        ($this->assignment->travel->tar_required ? 'Action' : 'Payment').
                        ' required for '.$this->assignment->travel->name.' travel'
                    )
                    ->text('mail.travel.assignmentcreated')
                    ->withSymfonyMessage(function (Email $email): void {
                        $email->replyTo(
                            $this->assignment->travel->primaryContact->name.
                            ' <'.$this->assignment->travel->primaryContact->gt_email.'>'
                        );
                    })
                    ->tag('travel-assignment-created')
                    ->metadata('assignment-id', strval($this->assignment->id));
    }
}
