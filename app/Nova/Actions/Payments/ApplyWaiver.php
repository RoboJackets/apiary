<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Http\Requests\NovaRequest;

class ApplyWaiver extends RecordPayment
{
    /**
     * The text to be used for the action's confirm button.
     *
     * @var string
     */
    public $confirmButtonText = 'Apply Waiver';

    /**
     * The text to be used for the action's confirmation text.
     *
     * @var string
     */
    public $confirmText = 'Waivers can only be applied for the full cost.';

    /**
     * The method of payment recorded by this action.
     *
     * @phan-suppress PhanUnreferencedProtectedClassConstant
     */
    public const string METHOD = 'waiver';

    /**
     * Get the fields available on the action.
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    #[\Override]
    public function fields(NovaRequest $request): array
    {
        return [
            Currency::make('Amount')
                ->rules('required')
                ->default(self::getPayableAmount($request))
                ->withMeta(['extraAttributes' => ['readonly' => true]]),
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
        return 'Applied in Nova';
    }
}
