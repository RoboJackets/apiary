<?php

declare(strict_types=1);

namespace App\Mail\Travel;

use App\Models\TravelAssignment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class TravelAssignmentReminder extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public readonly TravelAssignment $assignment)
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
                        'Reminder: '.$this->subjectLineCallToAction()
                        .' required for '.$this->assignment->travel->name.' travel'
                    )
                    ->text('mail.travel.assignmentreminder')
                    ->withSymfonyMessage(function (Email $email): void {
                        $email->replyTo(
                            $this->assignment->travel->primaryContact->name.
                            ' <'.$this->assignment->travel->primaryContact->gt_email.'>'
                        );
                    })
                    ->tag('travel-assignment-reminder')
                    ->metadata('assignment-id', strval($this->assignment->id));
    }

    private function subjectLineCallToAction(): string
    {
        if (
            $this->assignment->needs_docusign &&
            $this->assignment->is_paid &&
            $this->assignment->user->has_emergency_contact_information
        ) {
            return 'documents';
        } elseif ($this->assignment->needs_docusign) {
            return 'action';
        } elseif (! $this->assignment->user->has_emergency_contact_information) {
            return 'emergency contact information';
        } elseif (! $this->assignment->is_paid) {
            return 'payment';
        } else {
            throw new Exception('Unexpected state');
        }
    }
}
