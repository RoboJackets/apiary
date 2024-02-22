<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Payment;
use App\Models\TravelAssignment;
use App\Util\SquareCheckout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class PrefetchSquareCheckoutLinkForTravelAssignment implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly TravelAssignment $assignment)
    {
        $this->queue = 'square';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $assignment = $this->assignment;

        if ($assignment->is_paid) {
            return;
        }

        Cache::lock(name: $assignment->user->uid.'_payment', seconds: 120)->block(
            seconds: 60,
            callback: static function () use ($assignment): void {
                if ($assignment->payment()->count() === 0) {
                    $payment = new Payment();
                    // @phan-suppress-next-line PhanTypeMismatchPropertyProbablyReal
                    $payment->amount = 0;
                    $payment->method = 'square';
                    $payment->recorded_by = $assignment->user->id;
                    $payment->unique_id = Payment::generateUniqueId();
                    $payment->notes = 'Prefetch triggered by trip assignment creation';

                    $assignment->payment()->save($payment);
                } else {
                    $payment = $assignment
                        ->payment()
                        ->where('method', '=', 'square')
                        ->sole();
                }

                if ($payment->url !== null) {
                    return;
                }

                SquareCheckout::redirectToSquare(
                    $assignment->travel->fee_amount * 100,
                    $payment,
                    $assignment->user,
                    'Trip Fee',
                    $assignment->travel->name
                );
            }
        );
    }
}
