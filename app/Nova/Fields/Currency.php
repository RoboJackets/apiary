<?php

namespace App\Nova\Fields;

class Currency extends \Laravel\Nova\Fields\Currency
{
    /**
     * Check value for null value.
     *
     * @param  mixed $value
     * @return bool
     */
    protected function isNullValue($value)
    {
        if (is_null($value)) {
            return true;
        }

        return parent::isNullValue($value);
    }
}
