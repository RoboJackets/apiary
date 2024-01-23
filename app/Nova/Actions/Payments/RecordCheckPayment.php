<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use Carbon\Carbon;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class RecordCheckPayment extends RecordPayment
{
    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Record Check Payment';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Carefully examine the check and enter the details below. If there are validation errors, '
        .'do not accept the check, and return it to the member.';

    /**
     * The method of payment recorded by this action.
     *
     * @phan-suppress PhanUnreferencedProtectedClassConstant
     */
    public const METHOD = 'check';

    private const ALLOWABLE_PAY_TO_NAMES = [
        'georgia tech',
        'georgia institute of technology',
        'robojackets',
        'robojackets, inc.',
        'robojackets inc',
        'robo jackets',
    ];

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        $payable_amount = self::getPayableAmount($request);

        return [
            Date::make('Check Date')
                ->rules('required', 'date', static function (string $attribute, string $value, callable $fail): void {
                    // @phan-suppress-next-line PhanPossiblyFalseTypeArgument
                    if (Carbon::now()->lessThanOrEqualTo(Carbon::createFromTimestamp(strtotime($value)))) {
                        $fail('The check must be dated today or earlier. We cannot accept future-dated checks.');
                    }
                }),

            Number::make('Check Number')
                ->rules('required', 'integer', 'min:1')
                ->help(
                    'Enter the sequence number for the check. This is typically located in the top right of the check.'
                ),

            Text::make('Pay to the Order Of')
                ->rules('required', static function (string $attribute, string $value, callable $fail): void {
                    if (! in_array(strtolower($value), self::ALLOWABLE_PAY_TO_NAMES, true)) {
                        $fail(
                            'Checks must be written to either RoboJackets, Georgia Institute of Technology, or '
                            .'some variation of those.'
                        );
                    }
                }),

            Currency::make('Amount')
                ->rules(
                    'required',
                    static function (string $attribute, string $value, callable $fail) use ($payable_amount): void {
                        if (round(floatval($value), 2) !== floatval($payable_amount)) {
                            $fail('The amount must be exactly $'.$payable_amount.'.00.');
                        }
                    }
                ),

            Boolean::make('Amounts Match')
                ->help('Ensure the written and numeric amounts match.')
                ->rules('required', 'accepted')
                ->required(),

            Boolean::make('Signed')
                ->help('Ensure the check is signed by an account signer.')
                ->rules('required', 'accepted')
                ->required(),
        ];
    }

    /**
     * The note to add to the payment.
     *
     * @phan-suppress PhanTypeSuspiciousStringExpression
     */
    protected static function note(ActionFields $fields): string
    {
        return 'Check #'.$fields->check_number.
            ' dated '.$fields->check_date.
            ' payable to '.$fields->pay_to_the_order_of;
    }
}
