<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable as AuthorizableTrait;
use Laravel\Passport\Client;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

/**
 * An OAuth 2 client.
 *
 * @property string $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $secret
 * @property string|null $provider
 * @property string $redirect
 * @property bool $personal_access_client
 * @property bool $password_client
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\Laravel\Passport\AuthCode> $authCodes
 * @property-read int|null $auth_codes_count
 * @property-read string|null $plain_secret
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\App\Models\OAuth2AccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client wherePasswordClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client wherePersonalAccessClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereRedirect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OAuth2Client whereUserId($value)
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class OAuth2Client extends Client implements AuthorizableContract
{
    use AuthorizableTrait;
    use HasPermissions;
    use HasRoles;

    protected string $guard_name = 'web';

    /**
     * Determine if the client should skip the authorization prompt.
     */
    #[\Override]
    public function skipsAuthorization(Authenticatable $user, array $scopes): bool
    {
        return true;
    }
}
