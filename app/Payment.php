<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['method_presentation'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get all of the owning payable models.
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Payment model.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'recorded_by');
    }

    /**
     * Get the presentation-ready format of a Payment Method.
     *
     * @return string
     */
    public function getMethodPresentationAttribute()
    {
        $valueMap = [
            'cash' => 'Cash',
            'squarecash' => 'Square Cash',
            'check' => 'Check',
            'swipe' => 'Swiped Card',
            'square' => 'Square',
        ];

        $method = $this->method;

        if (array_key_exists($method, $valueMap)) {
            return $valueMap[$this->method];
        } else {
            return;
        }
    }
}
