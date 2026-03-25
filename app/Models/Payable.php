<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A model that can have payments associated.
 */
interface Payable
{
    /**
     * Whether the payable is fully paid.
     *
     * @psalm-mutation-free
     */
    public function getIsPaidAttribute(): bool;

    /**
     * The total amount due, if not paid, in whole US Dollars.
     *
     * @psalm-mutation-free
     */
    public function getPayableAmountAttribute(): int;

    /**
     * The User associated with this Payable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, self>
     *
     * @psalm-mutation-free
     */
    public function user(): BelongsTo;

    /**
     * The Payment(s) associated with this Payable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<\App\Models\Payment, self>
     *
     * @psalm-mutation-free
     */
    public function payment(): MorphMany;

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     *
     * @psalm-mutation-free
     */
    public function getMorphClass();
}
