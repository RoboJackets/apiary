<?php

declare(strict_types=1);

namespace App\Mail\Attendance;

use App\Models\AttendanceExport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email;

class Report extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The AttendanceExport for the email.
     *
     * @var AttendanceExport
     */
    public $export;

    /**
     * Create a new message instance.
     */
    public function __construct(AttendanceExport $export)
    {
        $this->export = $export;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('attendancereports@my.robojackets.org', 'RoboJackets')
            ->withSymfonyMessage(static function (Email $message): void {
                $message->getHeaders()->addTextHeader('Reply-To', 'RoboJackets Officers <officers@robojackets.org>');
            })->subject('RoboJackets Attendance Report Ending '.$this->export->end_time->format('n/j/Y'))
            ->markdown('mail.attendance.report');
    }
}
