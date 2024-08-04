<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Attendance;
use App\Notifiables\CoreNotifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class GlobalAttendanceNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(CoreNotifiable $notifiable): array
    {
        return $notifiable->routeNotificationForSlack($this) !== null ? ['slack'] : [];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(CoreNotifiable $notifiable): SlackMessage
    {
        // Today is Sunday, so go back 7 days to last Sunday at the start of the day.
        // Stop at yesterday at the end of the day.
        $startDay = now()->subDays(7)->startOfDay();
        $endDay = now()->subDays(1)->endOfDay();

        $total = Attendance::whereBetween('created_at', [$startDay, $endDay])
            ->selectRaw('count(distinct attendance.gtid) as aggregate, \'team\' as attendable_type, 0 as attendable_id')
            ->get()[0]->aggregate;

        // e.g. 15 members attended any team or event last week.
        $message = $total.' '.Str::plural('member', $total).' attended any team or event last week.';

        return (new SlackMessage())
            ->from(config('app.name'), ':robobuzz:')
            ->content($message);
    }
}
