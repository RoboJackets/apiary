<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingCampaign extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'status',
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * Get the recipients for this campaign.
     */
    public function recipients()
    {
        return $this->hasMany('App\RecruitingCampaignRecipient');
    }
}
