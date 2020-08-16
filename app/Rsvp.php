<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single RSVP to an event.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp query()
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Rsvp onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Rsvp withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|Rsvp withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property ?int $user_id the user that RSVPed to this event, if available
 * @property ?string $ip_address the IP that was used to submit the RSVP
 * @property ?string $response yes or no
 * @property ?string $source where the RSVP came from (e.g. email, website)
 * @property ?string $user_agent the user agent that was used to submit the rsvp
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $event_id the ID of the event that was RSVP'ed to
 * @property int $id
 * @property string|null $token
 *
 * @property-read \App\Event $event
 * @property-read \App\User $user
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
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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
