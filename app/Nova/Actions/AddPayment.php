<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter,Generic.Strings.UnnecessaryStringConcat.Found

namespace App\Nova\Actions;

use App\Notifications\Payment\ConfirmationNotification;
use App\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;

class AddPayment extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields  $fields
     * @param \Illuminate\Support\Collection  $models
     *
     * @return array<string,string>
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        if (count($models) > 1) {
            return Action::danger(
                'Payments cannot be entered for more than one transaction at a time. Contact the developers if '
                .'you think this is unreasonable.'
            );
        }

        if ($models->first()->is_paid) {
            $this->markAsFailed($models->first(), 'Transaction already paid in full');

            return Action::danger(
                'Transaction already paid in full. New transaction was not saved.'
            );
        }

        if (! $models->first()->package->is_active) {
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

        $package_amount = round($models->first()->package->cost, 2);
        $entered_amount = round($fields->amount, 2);

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
        $payment->amount = $entered_amount;
        $payment->payable_id = $models->first()->id;
        $payment->payable_type = \App\DuesTransaction::class;
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
                ->format('%.2n')
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
