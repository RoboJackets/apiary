<?php

declare(strict_types=1);

// phpcs:disable Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Nova\Actions;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Notifications\Payment\ConfirmationNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;

class AddPayment extends Action
{
    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\DuesTransaction>  $models
     * @return array<string,string>
     *
     * @phan-suppress PhanPossiblyNullTypeArgumentInternal
     * @phan-suppress PhanTypeMismatchArgumentInternal
     * @phan-suppress PhanTypeMismatchProperty
     * @phan-suppress PhanTypeSuspiciousStringExpression
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (count($models) > 1) {
            return Action::danger(
                'Payments cannot be entered for more than one transaction at a time. Contact the developers if '
                .'you think this is unreasonable.'
            );
        }

        $model = $models->first();

        if ($model->is_paid) {
            $this->markAsFailed($models->first(), 'Transaction already paid in full');

            return Action::danger(
                'Transaction already paid in full. New transaction was not saved.'
            );
        }

        if (is_a($model, DuesTransaction::class) && ! $model->package->is_active) {
            $this->markAsFailed($models->first(), 'Package no longer active');

            return Action::danger(
                'Associated package is no longer active.'
            );
        }

        // shouldn't happen but might if someone is abusing the API
        if (Auth::user()->cant('create-payments-'.$fields->method)) {
            $this->markAsFailed($models->first(), 'Not authorized to accept that payment method');

            return Action::danger(
                'You do not have permission to accept that payment method. Please contact a developer.'
            );
        }

        $package_amount = is_a(
            $model,
            DuesTransaction::class
        ) ? round($model->package->cost, 2) : intval($model->travel->fee_amount);

        $entered_amount = is_a(
            $model,
            DuesTransaction::class
        ) ? round($fields->amount, 2) : intval($fields->amount);

        if ('square' === $fields->method || 'swipe' === $fields->method) {
            if ($entered_amount !== round($package_amount + 3, 2)) {
                if ($entered_amount === $package_amount) {
                    $this->markAsFailed($models->first(), 'Missing transaction fee');

                    return Action::danger(
                        'Missing expected transaction fee - total should be '
                        .round($package_amount + 3, 2).', '.$entered_amount.' entered.'
                    );
                }

                $this->markAsFailed($models->first(), 'Unexpected amount (card transaction)');

                return Action::danger(
                    'Unexpected amount '.$entered_amount.' entered - should be '.round($package_amount + 3, 2)
                );
            }
        } else {
            if ($entered_amount !== $package_amount) {
                $this->markAsFailed($models->first(), 'Unexpected amount (non-card transaction)');

                return Action::danger(
                    'Unexpected amount '.$entered_amount.' entered - should be '.$package_amount
                );
            }
        }

        $payment = new Payment();
        $payment->recorded_by = Auth::user()->id;
        $payment->method = $fields->method;
        $payment->amount = strval($entered_amount);
        $payment->payable_id = $model->id;
        $payment->payable_type = $model->getMorphClass();
        $payment->notes = 'Added in Nova';
        $payment->save();

        $payment->payable->user->notify(new ConfirmationNotification($payment));

        return Action::message('The payment was added!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(): array
    {
        $allowed_payment_methods = [];

        foreach (Payment::$methods as $code => $display) {
            if (Auth::user()->cant('create-payments-'.$code)) {
                continue;
            }

            $allowed_payment_methods[$code] = $display;
        }

        return [
            Select::make('Payment Method', 'method')
                ->options($allowed_payment_methods)
                ->displayUsingLabels()
                ->creationRules('required'),

            Currency::make('Amount')
                ->help('Record actual amount of money collected, including processing fees. Credit/debit is +$3.')
                ->creationRules('required'),
        ];
    }

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;
}
