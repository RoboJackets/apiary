<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

namespace App\Providers;

use App\Models\OAuth2Client;
use App\Models\User;
use BadMethodCallException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use InvalidArgumentException;
use Ramsey\Uuid\Nonstandard\Uuid;

class UserOrClientUserProvider implements UserProvider
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function retrieveById($identifier): Authenticatable
    {
        if (is_integer($identifier) || is_numeric($identifier)) {
            return User::where('id', '=', $identifier)->sole();
        }

        if (Uuid::isValid($identifier)) {
            return OAuth2Client::where('id', '=', $identifier)->sole();
        }

        throw new InvalidArgumentException('Unrecognized identifier format');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function updateRememberToken(Authenticatable $user, #[\SensitiveParameter] $token)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function validateCredentials(Authenticatable $user, #[\SensitiveParameter] array $credentials)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function rehashPasswordIfRequired(
        Authenticatable $user,
        #[\SensitiveParameter] array $credentials,
        bool $force = false
    ) {
        throw new BadMethodCallException('Not implemented');
    }
}
