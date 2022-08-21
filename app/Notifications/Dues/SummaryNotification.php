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
        return $notifiable->routeNotificationForSlack($this) !== null
            && count($this->getPayments()) > 0 ? ['slack'] : [];
    }

    /**
     * Get the payments from yesterday.
     *
     * @return \Illuminate\Support\Collection<int,\App\Models\Payment>
     */
    private function getPayments(): Collection
    {
        // Get all of yesterday
        $startOfDay = now()->subDays(1)->startOfDay();
        $endOfDay = now()->subDays(1)->endOfDay();

        // Use updated_at because if you return to Square payment after it created a $0 payment it'll update it.
        // Nothing else will update it as far as I can tell.
        return Payment::whereBetween('updated_at', [$startOfDay, $endOfDay])
            ->where('amount', '>', 0)
            ->where('payable_type', DuesTransaction::getMorphClassStatic())
            ->get();
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(TreasurerNotifiable $team): SlackMessage
    {
        $numberFormatter = new NumberFormatter('en-US', NumberFormatter::CURRENCY);
        $payments = $this->getPayments();
        $num = $payments->count();
        $total = $numberFormatter->format($payments->sum('amount') - $payments->sum('processing_fee'));
        $methods = $payments->groupBy('method')
            ->sort(static function (Collection $a, Collection $b) {
                // Sort by quantity descending
                if ($a->count() === $b->count()) {
                    return 0;
                }

                return $a->count() > $b->count() ? -1 : 1;
            })->map(
                static fn (Collection $p, string $m): string => $p->count().' paid with '.Payment::$methods[$m]
            )->join(', ', ' and ');
        $packages = $payments->groupBy(
            static fn (Payment $payment): string => DuesTransaction::with('package')->withTrashed()->find(
                $payment->payable_id
            )->package->name
        )->sort(
            static fn (Collection $a, Collection $b): int => $b->count() - $a->count()
        )->map(
            static fn (Collection $payment, string $package): string => $payment->count().' paid for '.$package
        )->join(', ', ' and ');

        $active = User::active()->count();

        // e.g. 12 members paid dues yesterday, totaling $1,155.00 collected. 11 paid with Square Checkout and 1 paid
        // with a check. There are now 13 active members.
        $message = $num.' '.Str::plural('member', $num).' paid dues yesterday, totaling '.$total.' collected. ';
        $message .= $methods.'. '.$packages;
        $message .= '. There '.($active === 1 ? 'is' : 'are').' now '.$active.' active '.Str::plural('member', $active);
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
