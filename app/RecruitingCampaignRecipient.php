<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingCampaignRecipient extends Model
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the user that owns the phone.
     */
    public function recruitingCampaign()
    {
        return $this->belongsTo(\App\RecruitingCampaign::class);
    }

    /**
     * Get the user that owns the phone.
     */
    public function recruitingVisit()
    {
        return $this->belongsTo(\App\RecruitingVisit::class);
    }

    /**
     * Get the user that is related to the model.
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
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

    /**
     * Get the visit token for the model.
     */
    public function getVisitToken()
    {
        return $this->recruitingVisit->visit_token ?: null;
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     * @return array
     */
    public function getRelationshipPermissionMap()
    {
        return [
            'recruitingCampaign' => 'recruiting-campaigns',
            'recruitingVisit' => 'recruiting-visits',
            'user' => 'users',
        ];
    }
}
