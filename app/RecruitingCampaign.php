<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingCampaign extends Model
{
    use SoftDeletes;

    /**
     * Get the user that owns the campaign.
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
