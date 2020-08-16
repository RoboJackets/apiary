<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DuesPackage;
use App\Team;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TeamAttendanceNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(Team $notifiable): array
    {
        return null !== $notifiable->routeNotificationForSlack($this)
            && null !== $notifiable->slack_private_channel_id ? ['slack'] : [];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(Team $team): SlackMessage
    {
        // Today is Sunday, so go back 7 days to last Sunday at the start of the day.
        // Stop at yesterday at the end of the day.
        $startDay = now()->subDays(7)->startOfDay();
        $endDay = now()->subDays(1)->endOfDay();

        $total = $team->attendance()->whereBetween('created_at', [$startDay, $endDay])
            ->selectRaw('count(distinct attendance.gtid) as aggregate')
            ->get()[0]->aggregate;
        $unknown = $team->attendance()->whereBetween('created_at', [$startDay, $endDay])->doesntHave('attendee')
            ->selectRaw('count(distinct attendance.gtid) as aggregate')
            ->get()[0]->aggregate;
        $unknown = intval($unknown); // Silences Phan warnings
        $knownAttendance = $team->attendance()->whereBetween('created_at', [$startDay, $endDay])->has('attendee')
            ->get();
        $duesPackageAvailable = DuesPackage::availableForPurchase()->active()->count() > 0;

        $inactiveNames = $knownAttendance->pluck('attendee')
            ->unique()
            ->filter(static function (User $user): bool {
                return false === $user->is_active;
            })->pluck('name');

        $inactive = $inactiveNames->count() + $unknown;

        if ($unknown > 0) {
            $inactiveNames = $inactiveNames->concat([
                $unknown.' '.Str::plural('person', $unknown).' who'.(1 === $unknown ? ' has' : ' have')
                    .' never logged in to MyRoboJackets',
            ]);
        }

        // e.g. 15 members attended BattleBots last week.
        $message = $total.' '.Str::plural('member', $total).' attended '.$team->name.' last week.';

        // e.g. Of those attendees, the following 1 person has not paid dues.
        // e.g. Of those attendees, the following 3 people have not paid dues.
        $inactiveTitle = 'Of those attendees, the following '.$inactive.' '.Str::plural('person', $inactive);
        $inactiveTitle .= (1 === $inactive ? ' has' : ' have').' not paid dues:';
        $inactiveNames = $inactiveNames->join(', ', ' and ');

        $slackMessage = (new SlackMessage())
            ->from(config('app.name'), ':robobuzz:')
            ->to($team->slack_private_channel_id)
            ->content($message);

        if ($inactive > 0 && $duesPackageAvailable) {
            $slackMessage = $slackMessage->warning()
                ->attachment(static function (SlackAttachment $attachment) use ($inactiveTitle, $inactiveNames): void {
                    $attachment->title($inactiveTitle)->content($inactiveNames);
                });
        }

        return $slackMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string,string>
     */
    public function toArray(Team $notifiable): array
    {
        return [];
    }
}
