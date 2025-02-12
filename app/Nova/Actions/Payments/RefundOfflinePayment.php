<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class RefundOfflinePayment extends Action
{
    public const REFUNDABLE_OFFLINE_PAYMENT_METHODS = [
        'cash',
        'check',
    ];

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Refund Payment';

    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Refund Payment';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Provide a reason for refunding this payment.';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * The metadata for the element.
     *
     * @var array<string, bool>
     */
    public $meta = [
        'destructive' => true,
    ];

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Payment>  $models
     *
     * @phan-suppress PhanTypeMismatchPropertyProbablyReal
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $payment = $models->sole();

        if (! in_array($payment->method, self::REFUNDABLE_OFFLINE_PAYMENT_METHODS, true)) {
            $this->markAsFailed($payment, Str::title($payment->method).' payments may not be refunded.');

            return self::danger(Str::title($payment->method).' payments may not be refunded.');
        }

        if (floatval($payment->amount) === 0.0) {
            $this->markAsFailed($payment, 'This payment was already refunded.');

            return self::danger('This payment was already refunded.');
        }

        if (Auth::user()->cant('refund-payments')) {
            $this->markAsFailed($payment, 'You do not have access to refund payments.');

            return self::danger('You do not have access to refund payments.');
        }

        $payment->amount = 0;
        $payment->notes .= '; refunded with reason "'.$fields->reason.'"';
        $payment->save();

        return self::message('Recorded refund!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        $payment = Payment::whereId($request->resourceId ?? $request->resources)->sole();

        return [
            Currency::make('Refund Amount')
                ->default(static fn (): string => $payment->amount)
                ->required()
                ->help('Partial refunds aren\'t supported.')
                ->readonly(),

            Text::make('Reason')
                ->required()
                ->rules('required', 'max:192'),
        ];
    }
}
