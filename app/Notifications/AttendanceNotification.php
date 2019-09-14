<?php

namespace App\Notifications;

use App\DuesPackage;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class AttendanceNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable->routeNotificationForSlack($this) && $notifiable->slack_private_channel_id) {
            return ['slack'];
        } else {
            return [];
        }
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($team)
    {
        // e.g. today is Sunday, go back 7 days to last Sunday at the start of the day. Stop at yesterday at the end of the day.
        $startDay = now()->subDays(7)->startOfDay();
        $endDay = now()->subDays(1)->endOfDay();

        $total = $team->attendance()->whereBetween('created_at', [$startDay, $endDay])
            ->selectRaw('count(distinct attendance.gtid) as aggregate')
            ->get()[0]->aggregate;
        $unknown = $team->attendance()->whereBetween('created_at', [$startDay, $endDay])->doesntHave('attendee')
            ->selectRaw('count(distinct attendance.gtid) as aggregate')
            ->get()[0]->aggregate;
        $knownAttendance = $team->attendance()->whereBetween('created_at', [$startDay, $endDay])->has('attendee')->get();
        $duesPackageAvailable = DuesPackage::availableForPurchase()->active()->count() > 0;

        $inactiveNames = $knownAttendance->pluck('attendee')
            ->unique()
            ->filter(function ($user) {
                return ! $user->is_active;
            })->pluck('name');

        $inactive = $inactiveNames->count() + $unknown;
        $active = $total - $inactive;

        if ($unknown > 0) {
            $inactiveNames = $inactiveNames->concat([$unknown.' '.Str::plural('person', $unknown).' who'.($unknown == 1 ? ' has' : ' have').' never logged in to MyRoboJackets']);
        }

        // e.g. 15 members attended last week.
        $message = $total.' '.Str::plural('member', $total).' attended last week.';

        // e.g. Of those attendees, the following 1 person has not paid dues.
        // e.g. Of those attendees, the following 3 people have not paid dues.
        $inactiveTitle = 'Of those attendees, the following '.$inactive.' '.Str::plural('person', $inactive);
        $inactiveTitle = $inactiveTitle.($inactive == 1 ? ' has' : ' have').' not paid dues:';
        $inactiveNames = $inactiveNames->join(', ', ' and ');

        $slackMessage = (new SlackMessage)
            ->from(config('app.name'), ':robobuzz:')
            ->to($team->slack_private_channel_id)
            ->content($message);

        if ($inactive > 0 && $duesPackageAvailable) {
            $slackMessage = $slackMessage->warning()->attachment(function ($attachment) use ($inactiveTitle, $inactiveNames) {
                $attachment->title($inactiveTitle)->content($inactiveNames);
            });
        }

        return $slackMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
