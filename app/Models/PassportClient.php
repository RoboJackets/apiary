<?php

namespace App\Models;

use Laravel\Passport\Client as BaseClient;

class PassportClient extends BaseClient
{
    protected $casts = [
        'grant_types' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
    ];

    public function firstParty(): bool
    {
        return parent::firstParty();
    }

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization(): bool
    {
        return $this->firstParty();
    }
}
