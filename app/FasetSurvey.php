<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FasetSurvey extends Model
{
    use SoftDeletes;
    
    /**
     *  Get the FASET Responses associated with this FASET Survey
     */
    public function fasetResponses()
    {
        return $this->hasMany('App\FasetResponse');
    }
}
