<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient {
    /**
     * Determine if the client should skip the authorization prompt.
     */
    public function skipsAuthorization(): bool
    {
        return true;
    }
}
