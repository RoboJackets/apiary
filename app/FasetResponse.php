<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FasetResponse extends Model
{
    use SoftDeletes;

    /**
     *  Get the FASET visit associated with this FASET response.
     */
    public function fasetVisit()
    {
        return $this->belongsTo(\App\FasetVisit::class);
    }

    protected $fillable = ['faset_survey_id', 'response'];
}
