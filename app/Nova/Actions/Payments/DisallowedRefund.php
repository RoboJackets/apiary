<?php

declare(strict_types=1);

namespace App\Nova\Actions\Payments;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DisallowedRefund extends Action
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Refund Payment';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnDetail = true;

    /**
     * Determine where the action redirection should be without confirmation.
     *
     * @var bool
     */
    public $withoutConfirmation = true;

    /**
     * The metadata for the element.
     *
     * @var array<string, bool>
     */
    public $meta = [
        'destructive' => true,
    ];

    public function __construct(private readonly string $reason)
    {
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Illuminate\Support\Collection<int,\App\Models\Payment>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $this->markAsFailed($models->sole(), $this->reason);

        return self::danger($this->reason);
    }
}
