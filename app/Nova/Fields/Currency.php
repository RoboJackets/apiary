<?php

declare(strict_types=1);

namespace App\Nova\Fields;

class Currency extends \Laravel\Nova\Fields\Currency
{
    /**
     * Check value for null value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isNullValue($value)
    {
        if (null === $value) {
            return true;
        }

        return parent::isNullValue($value);
    }
}
