<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingCampaignRecipient extends Model
{
    use SoftDeletes;
    use Notifiable;

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

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email_address;
    }
}
