<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

class Payment extends Model
{
    use Actionable;
    use SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['method_presentation'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * Get all of the owning payable models.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Payment model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'recorded_by');
    }

    /**
     * Get the presentation-ready format of a Payment Method.
     *
     * @return string
     */
    public function getMethodPresentationAttribute(): string
    {
        $valueMap = [
            'cash' => 'Cash',
            'squarecash' => 'Square Cash',
            'check' => 'Check',
            'swipe' => 'Swiped Card',
            'square' => 'Square',
        ];

        $method = $this->method;

        return array_key_exists($method, $valueMap) ? $valueMap[$this->method] : '';
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'user' => 'users',
            'payable' => 'dues-transactions',
        ];
    }
}
