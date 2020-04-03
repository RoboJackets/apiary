<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Notifications;

use App\AttendanceExport;
use App\Notifiables\CoreOfficersNotifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class WeeklyAttendanceEmailConfirmation extends Notification
{
    use Queueable;

    /**
     * The AttendanceExport relevant to this.
     *
     * @var \App\AttendanceExport
     */
    public $export;

    /**
     * True if not sending due to no attendance.
     *
     * @var bool
     */
    public $notSending;

    /**
     * Create a new notification instance.
     */
    public function __construct(AttendanceExport $export, bool $notSending)
    {
        $this->export = $export;
        $this->notSending = $notSending;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(CoreOfficersNotifiable $notifiable): array
    {
        return null !== $notifiable->routeNotificationForSlack($this) ? ['slack'] : [];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(CoreOfficersNotifiable $notifiable): SlackMessage
    {
        $message = $this->notSending ? 'The weekly attenadnce report email will not be sent as no attendance was '.
            'recorded in the last seven days.' : 'The weekly attendance email has been sent to '.
            config('services.attendance_email').'. The report includes attendance from '.
            $this->export->start_time->format('l, n/j/Y \a\t g:iA').' to '.
            $this->export->end_time->format('l, n/j/Y \a\t g:iA').'.';

        return (new SlackMessage())
            ->from(config('app.name'), ':robobuzz:')
            ->content($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string,string>
     */
    public function toArray(CoreOfficersNotifiable $notifiable): array
    {
        return [];
    }
}
