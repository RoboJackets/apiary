<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Notifications\Dues;

use App\User;
use App\DuesPackage;
use App\DuesTransaction;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;

class SummaryNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param TreasurerNotifiable  $notifiable
     *
     * @return array<string>
     */
    public function via(TreasurerNotifiable $notifiable): array
    {
        return $notifiable->routeNotificationForSlack($this) && count($this->getPayments()) > 0 ? ['slack'] : [];
    }

    /**
     * Get the payments from yesterday.
     *
     * @return array<Payment>
     */
    private function getPayments(): array
    {
        // Get all of yesterday
        $startOfDay = now()->subDays(1)->startOfDay();
        $endOfDay = now()->subDays(1)->endOfDay();

        // Use updated_at because if you return to Square payment after it created a $0 payment it'll update it.
        // Nothing else will update it as far as I can tell.
        $payments = Payment::whereBetween('updated_at', [$startOfDay, $endOfDay])
            ->where('amount', '>', 0)
            ->get();

        return $payments->toArray();
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param TreasurerNotifiable  $team
     *
     * @return SlackMessage
     */
    public function toSlack(TreasurerNotifiable $team): SlackMessage
    {

        $payments = collect($this->getPayments());
        $num = $payments->count();
        $total = money_format('$%.2n', $payments->sum('amount'));
        $methods = $payments->groupBy('method')
            ->sort(function (array<Payment> $a, array<Payment> $b) {
                // Sort by quantity descending
                if (count($a) == count($b)) {
                    return 0;
                }

                return (count($a) > count($b)) ? -1 : 1;
            })->mapWithKeys(static function (array<Payment> $payment, string $method) {
                $paymentMethods = [
                    'cash' => 'cash',
                    'squarecash' => 'Square Cash',
                    'check' => 'a check',
                    'swipe' => 'a swiped card',
                    'square' => 'Square Checkout',
                ];

                return count($payment).' paid with '.$paymentMethods[$method];
            })->join(', ', ' and ');
        $packages = $payments->groupBy('package.name')
            ->sort(function (array<Payment> $a, array<Payment> $b) {
                // Sort by quantity descending
                if (count($a) == count($b)) {
                    return 0;
                }

                return (count($a) > count($b)) ? -1 : 1;
            })->mapWithKeys(static function (array<Payment> $payment, string $package) {
                return count($payment).' paid for '.$package;
            })->join(', ', ' and ');

        $active = User::active()->count();

        // e.g. 12 members paid dues yesterday, totaling $1,155.00 collected. 11 paid with Square Checkout and 1 paid with a check. There are now 13 active members.
        $message = $num.' '.Str::plural('member', $num).' paid dues yesterday, totaling '.$total.' collected. ';
        $message .= $methods.' '.$packages;
        $message .= ' There '.(1 === $active ? 'is' : 'are').' now '.$active.' active '.Str::plural('member', $active);
        $message .= '.';

        return (new SlackMessage())
            ->from(config('app.name'), ':robobuzz:')
            ->content($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param Team $notifiable
     *
     * @return array<string,string>
     */
    public function toArray(TreasurerNotifiable $notifiable): array
    {
        return [];
    }
}
