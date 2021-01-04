<?php

declare(strict_types=1);

namespace App\Notifications\Dues;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\User;
use App\Notifiables\TreasurerNotifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NumberFormatter;

class SummaryNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via(TreasurerNotifiable $notifiable): array
    {
        return null !== $notifiable->routeNotificationForSlack($this)
            && count($this->getPayments()) > 0 ? ['slack'] : [];
    }

    /**
     * Get the payments from yesterday.
     *
     * @return Collection<Payment>
     */
    private function getPayments(): Collection
    {
        // Get all of yesterday
        $startOfDay = now()->subDays(1)->startOfDay();
        $endOfDay = now()->subDays(1)->endOfDay();

        // Use updated_at because if you return to Square payment after it created a $0 payment it'll update it.
        // Nothing else will update it as far as I can tell.
        $payments = Payment::whereBetween('updated_at', [$startOfDay, $endOfDay])
            ->where('amount', '>', 0)
            ->where('payable_type', DuesTransaction::getMorphClassStatic())
            ->get();

        return $payments;
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(TreasurerNotifiable $team): SlackMessage
    {
        $numberFormatter = new NumberFormatter('en-US', NumberFormatter::CURRENCY);
        $payments = $this->getPayments();
        $num = $payments->count();
        $total = $numberFormatter->format($payments->sum('amount'));
        $methods = $payments->groupBy('method')
            ->sort(static function (Collection $a, Collection $b) {
                // Sort by quantity descending
                if ($a->count() === $b->count()) {
                    return 0;
                }

                return $a->count() > $b->count() ? -1 : 1;
            })->map(static function (Collection $payment, string $method): string {
                return $payment->count().' paid with '.Payment::$methods[$method];
            })->join(', ', ' and ');
        $packages = $payments->groupBy(static function (Payment $payment): string {
            // We know it's a DuesTransaction because of filtering in getPayments. Include trashed because in some
            // cases transactions can be trashed, but payments that aren't still refer to them.
            return DuesTransaction::with('package')->withTrashed()->find($payment->payable_id)->package->name;
        })->sort(static function (Collection $a, Collection $b): int {
            // Sort by quantity descending
            if ($a->count() === $b->count()) {
                return 0;
            }

            return $a->count() > $b->count() ? -1 : 1;
        })->map(static function (Collection $payment, string $package): string {
            return $payment->count().' paid for '.$package;
        })->join(', ', ' and ');

        $active = User::active()->count();

        // e.g. 12 members paid dues yesterday, totaling $1,155.00 collected. 11 paid with Square Checkout and 1 paid
        // with a check. There are now 13 active members.
        $message = $num.' '.Str::plural('member', $num).' paid dues yesterday, totaling '.$total.' collected. ';
        $message .= $methods.'. '.$packages;
        $message .= '. There '.(1 === $active ? 'is' : 'are').' now '.$active.' active '.Str::plural('member', $active);
        $message .= '.';

        return (new SlackMessage())
            ->from(config('app.name'), ':robobuzz:')
            ->content($message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string,string>
     */
    public function toArray(TreasurerNotifiable $notifiable): array
    {
        return [];
    }
}
