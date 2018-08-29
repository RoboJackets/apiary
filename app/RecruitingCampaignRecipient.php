<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingCampaignRecipient extends Model
{
    use SoftDeletes;

    /**
     * Get the user that owns the phone.
     */
    public function recruitingCampaign()
    {
        return $this->belongsTo('App\RecruitingCampaign');
    }

    /**
     * Get the user that owns the phone.
     */
    public function recruitingVisit()
    {
        return $this->belongsTo('App\RecruitingVisit');
    }

    /**
     * Get the user that is related to the model.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
