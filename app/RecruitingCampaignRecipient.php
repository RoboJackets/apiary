<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitingCampaignRecipient extends Model
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
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
    public function recruitingCampaign(): BelongsTo
    {
        return $this->belongsTo(\App\RecruitingCampaign::class);
    }

    /**
     * Get the user that owns the phone.
     */
    public function recruitingVisit(): BelongsTo
    {
        return $this->belongsTo(\App\RecruitingVisit::class);
    }

    /**
     * Get the user that is related to the model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        return $this->email_address;
    }

    /**
     * Get the visit token for the model.
     */
    public function getVisitToken(): string
    {
        return $this->recruitingVisit->visit_token;
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'recruitingCampaign' => 'recruiting-campaigns',
            'recruitingVisit' => 'recruiting-visits',
            'user' => 'users',
        ];
    }
}
