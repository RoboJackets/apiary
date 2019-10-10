<?php

declare(strict_types=1);

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Field;

class Hidden extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'hidden-field';

    /**
     * Set a monospaced font.
     *
     * @return $this
     */
    public function monospaced()
    {
        return $this->withMeta(['monospaced' => true]);
    }
}
