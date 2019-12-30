<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single RSVP to an event.
 *
 * @property ?int $user_id the user that RSVPed to this event, if available
 * @property ?string $ip_address the IP that was used to submit the RSVP
 * @property ?string $user_agent the user agent that was used to submit the rsvp
 * @property int $event_id the ID of the event that was RSVP'ed to
 * @property ?string $source where the RSVP came from (e.g. email, website)
 * @property ?string $response yes or no
 */
class Rsvp extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [
        'id', 'created_at', 'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(\App\Event::class);
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'user' => 'users',
            'event' => 'events',
        ];
    }
}
