<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FasetResponse extends Model
{
    /**
     *  Get the FASET visit associated with this FASET response
     */
    public function fasetVisit()
    {
        return $this->belongsTo('App\FasetVisit');
    }

    /**
     *  Get the FASET survey question associated with this FASET response
     */
    public function fasetSurvey()
    {
        return $this->belongsTo('App\FasetSurvey');
    }

    protected $fillable = ['faset_survey_id', 'response'];
}
