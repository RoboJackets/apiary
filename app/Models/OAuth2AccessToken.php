<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Passport\Token;

/**
 * Represents a token that can be used to access the API.
 *
 * @property string $id
 * @property int|null $user_id
 * @property string $client_id
 * @property string|null $name
 * @property array|null $scopes
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property-read \App\Models\OAuth2Client|null $client
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereScopes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2AccessToken whereUserId($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class OAuth2AccessToken extends Token
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'scopes' => 'array',
        'revoked' => 'bool',
    ];
}
