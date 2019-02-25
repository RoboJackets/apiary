<?php

namespace App\Nova\Actions;

use App\Event;
use App\Payment;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\Currency;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\Payment\ConfirmationNotification;

class AddPayment extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (count($models) > 1) {
            return Action::danger(
                'Payments cannot be entered for more than one transaction at a time. Contact the developers if you think this is unreasonable.'
            );
        }
        // shouldn't happen but might if someone is abusing the API
        if (Auth::user()->cant('create-payments-'.$fields->method)) {
            $this->markAsFailed($models->first(), null);

            return Action::danger(
                'You do not have permission to accept that payment method. Please contact a developer.'
            );
        }

        if ($fields->method === 'square' || $fields->method === 'swipe') {
            if ($fields->amount !== ($models->first()->package()->get()->first()->cost) + 3) {
                $this->markAsFailed($models->first(), null);

                return Action::danger(
                    'Missing expected transaction fee - total should be '.(($models->first()->package()->get()->first()->cost) + 3).', '.$fields->amount.' entered.'
                );
            }
        } else {
            if (floatval($fields->amount) !== floatval($models->first()->package()->get()->first()->cost)) {
                $this->markAsFailed($models->first(), null);

                return Action::danger(
                    'Unexpected amount '.$fields->amount.' entered - should be '.($models->first()->package()->get()->first()->cost)
                );
            }
        }

        if ($models->first()->is_paid) {
            $this->markAsFailed($models->first(), null);

            return Action::danger(
                'Transaction already paid in full. New transaction was not saved.'
            );
        }

        if (! $models->first()->package->is_active) {
            $this->markAsFailed($models->first(), null);

            return Action::danger(
                'Associated package is no longer active.'
            );
        }

        $payment = new Payment();
        $payment->recorded_by = Auth::user()->id;
        $payment->method = $fields->method;
        $payment->amount = $fields->amount;
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
     * @return array
     */
    public function fields()
    {
        $payment_methods = [
            'cash' => 'Cash',
            'squarecash' => 'Square Cash',
            'check' => 'Check',
            'swipe' => 'Swiped Card',
            'square' => 'Square Checkout',
        ];

        $allowed_payment_methods = [];

        foreach ($payment_methods as $code => $display) {
            if (Auth::user()->can('create-payments-'.$code)) {
                $allowed_payment_methods[$code] = $display;
            }
        }

        return [
            Select::make('Payment Method', 'method')
                ->options($allowed_payment_methods)
                ->displayUsingLabels()
                ->creationRules('required'),

            Currency::make('Amount')
                ->format('%.2n')
                ->help('Record the actual amount of money being collected, including surcharges or processing fees.')
                ->creationRules('required'),
        ];
    }
}
