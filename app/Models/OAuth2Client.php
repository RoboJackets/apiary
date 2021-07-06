<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Passport\Client as PassportClient;

/**
 * An OAuth 2 client.
 *
 * @property int $user_id the user that owns this client
 */
class OAuth2Client extends PassportClient
{
    /**
     * Determine if the client should skip the authorization prompt.
     */
    public function skipsAuthorization(): bool
    {
        return true;
    }
}
