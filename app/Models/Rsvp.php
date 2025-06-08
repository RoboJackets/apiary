<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single RSVP to an event.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property int $event_id
 * @property string|null $source
 * @property string|null $token
 * @property string|null $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rsvp newQuery()
 * @method static \Illuminate\Database\Query\Builder|Rsvp onlyTrashed()
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
 * @method static \Illuminate\Database\Query\Builder|Rsvp withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Rsvp withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
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
        'id',
        'created_at',
        'updated_at',
    ];

    public const array RELATIONSHIP_PERMISSIONS = [
        'user' => 'read-users',
        'event' => 'read-events',
    ];

    /**
     * Get the user that RSVPed.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Rsvp>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event they RSVPed to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Event, \App\Models\Rsvp>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
