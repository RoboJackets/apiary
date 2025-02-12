<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use App\Models\DuesTransaction;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\TravelAssignment;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

abstract class RecordPayment extends Action
{
    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Indicates if need to skip log action events for models.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Payable>  $models
     *
     * @phan-suppress PhanUndeclaredConstantOfClass
     * @phan-suppress PhanTypeMismatchProperty
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $payable = $models->sole();

        if ($payable->is_paid) {
            return self::danger('This '.self::prettyPayableName($payable).' is already paid in full.');
        }

        if (Auth::user()->id === $payable->user->id) {
            return self::danger(
                'You may not record a '.static::METHOD.' payment for your own '.self::prettyPayableName($payable).'.'
            );
        }

        if (! $payable->user->signed_latest_agreement) {
            return self::danger('This member has not signed the latest membership agreement.');
        }

        if (is_a($payable, DuesTransaction::class) && ! $payable->package->is_active) {
            return self::danger('The package associated with this transaction is inactive.');
        }

        if (Auth::user()->cant('create-payments-'.static::METHOD)) {
            return self::danger('You do not have access to record '.static::METHOD.' payments.');
        }

        $payable_amount = $payable->payable_amount;
        $entered_amount = $fields->amount;

        if (round(floatval($entered_amount), 2) !== floatval($payable_amount)) {
            return self::danger('Unexpected amount $'.$entered_amount.' entered - should be $'.$payable_amount);
        }

        $payment = new Payment();
        $payment->recorded_by = Auth::user()->id;
        $payment->method = static::METHOD;
        $payment->amount = $entered_amount;
        $payment->payable_id = $payable->id;
        $payment->payable_type = $payable->getMorphClass();
        $payment->notes = static::note($fields);
        $payment->save();

        return Action::message('Recorded '.static::METHOD.' payment!');
    }

    protected static function getPayableAmount(NovaRequest $request): int
    {
        $resourceType = $request->resource;
        $resourceId = $request->resourceId ?? $request->resources;

        if ($resourceType === null) {
            throw new Exception('resourceType is null');
        }

        if ($resourceId === null) {
            throw new Exception('resourceId is null');
        }

        if ($resourceType === \App\Nova\DuesTransaction::uriKey()) {
            return intval(DuesTransaction::whereId($resourceId)->sole()->package->cost);
        } elseif ($resourceType === \App\Nova\TravelAssignment::uriKey()) {
            return intval(TravelAssignment::whereId($resourceId)->sole()->travel->fee_amount);
        }

        throw new Exception('Unexpected resourceType '.$resourceType);
    }

    private static function prettyPayableName(Payable $payable): string
    {
        if (is_a($payable, DuesTransaction::class)) {
            return 'dues transaction';
        } elseif (is_a($payable, TravelAssignment::class)) {
            return 'travel assignment';
        }

        throw new Exception('Unexpected payable type');
    }

    /**
     * The note to add to the payment.
     */
    abstract protected static function note(ActionFields $fields): string;
}
