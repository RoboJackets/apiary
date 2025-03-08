<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Http\Requests\NovaRequest;

class RecordCashPayment extends RecordPayment
{
    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Record Cash Payment';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Enter the amount of cash collected.';

    /**
     * The method of payment recorded by this action.
     *
     * @phan-suppress PhanUnreferencedProtectedClassConstant
     */
    public const string METHOD = 'cash';

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        $payable_amount = self::getPayableAmount($request);

        return [
            Currency::make('Amount')
                ->rules(
                    'required',
                    static function (string $attribute, string $value, callable $fail) use ($payable_amount): void {
                        if (round(floatval($value), 2) !== floatval($payable_amount)) {
                            $fail('The amount must be exactly $'.$payable_amount.'.00.');
                        }
                    }
                ),
        ];
    }

    /**
     * The note to add to the payment.
     *
     * @phan-suppress PhanUnusedProtectedMethodParameter
     */
    #[\Override]
    protected static function note(ActionFields $fields): string
    {
        return 'Recorded in Nova';
    }
}
