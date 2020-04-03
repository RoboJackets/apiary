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
     * Create a new notification instance.
     */
    public function __construct(AttendanceExport $export)
    {
        $this->export = $export;
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
        return (new SlackMessage())
            ->from(config('app.name'), ':robobuzz:')
            ->content(
                'The weekly attendance email has been sent to '.config('services.attendance_email').'. The report '.
                'includes attendance from '.$this->export->start_time->format('l, n/j/Y \a\t g:iA').' to '.
                $this->export->end_time->format('l, n/j/Y \a\t g:iA').'.'
            );
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
