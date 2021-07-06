<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Passport\Token;

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
