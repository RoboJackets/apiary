<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingResponse extends Model
{
    use SoftDeletes;

    /**
     *  Get the recruiting visit associated with this recruiting response.
     */
    public function recruitingVisit()
    {
        return $this->belongsTo('App\RecruitingVisit');
    }

    protected $fillable = ['recruiting_survey_id', 'response'];
}
