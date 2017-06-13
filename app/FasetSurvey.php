<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FasetSurvey extends Model
{
    /**
     *  Get the FASET Responses associated with this FASET Survey
     */
    public function fasetResponses()
    {
        return $this->hasMany('App\FasetResponse');
    }
}
