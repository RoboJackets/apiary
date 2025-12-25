<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OAuth2AccessToken;
use App\Models\OAuth2Client;
use App\Nova\Actions\RevokeOAuth2Token;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Passport\RefreshToken;
use Tests\TestCase;

final class RevokeOAuth2TokenTest extends TestCase
{
    public function test_revokes_single_token(): void
    {
        $user = $this->getTestUser(['admin']);
        $client = OAuth2Client::forceCreate([
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => ['http://localhost'],
            'revoked' => false,
            'grant_types' => ['authorization_code'],
        ]);

        $token = OAuth2AccessToken::forceCreate([
            'id' => 'test-token-id-1',
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Test Token',
            'scopes' => [],
            'revoked' => false,
            'expires_at' => now()->addYear(),
        ]);

        $this->assertFalse($token->revoked);

        $action = new RevokeOAuth2Token();
        $action->handle(new ActionFields(collect(), collect()), Collection::make([$token]));

        $token->refresh();
        $this->assertTrue($token->revoked);
    }

    public function test_revokes_multiple_tokens(): void
    {
        $user = $this->getTestUser(['admin']);
        $client = OAuth2Client::forceCreate([
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => ['http://localhost'],
            'revoked' => false,
            'grant_types' => ['authorization_code'],
        ]);

        $token1 = OAuth2AccessToken::forceCreate([
            'id' => 'test-token-id-1',
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Test Token 1',
            'scopes' => [],
            'revoked' => false,
            'expires_at' => now()->addYear(),
        ]);

        $token2 = OAuth2AccessToken::forceCreate([
            'id' => 'test-token-id-2',
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Test Token 2',
            'scopes' => [],
            'revoked' => false,
            'expires_at' => now()->addYear(),
        ]);

        $action = new RevokeOAuth2Token();
        $action->handle(new ActionFields(collect(), collect()), Collection::make([$token1, $token2]));

        $token1->refresh();
        $token2->refresh();

        $this->assertTrue($token1->revoked);
        $this->assertTrue($token2->revoked);
    }

    public function test_revokes_associated_refresh_token(): void
    {
        $user = $this->getTestUser(['admin']);
        $client = OAuth2Client::forceCreate([
            'name' => 'Test Client',
            'secret' => 'test-secret',
            'redirect_uris' => ['http://localhost'],
            'revoked' => false,
            'grant_types' => ['authorization_code'],
        ]);

        $token = OAuth2AccessToken::forceCreate([
            'id' => 'test-token-id-with-refresh',
            'user_id' => $user->id,
            'client_id' => $client->id,
            'name' => 'Test Token',
            'scopes' => [],
            'revoked' => false,
            'expires_at' => now()->addYear(),
        ]);

        $refreshToken = RefreshToken::forceCreate([
            'id' => 'test-refresh-token-id',
            'access_token_id' => $token->id,
            'revoked' => false,
            'expires_at' => now()->addYear(),
        ]);

        $action = new RevokeOAuth2Token();
        $action->handle(new ActionFields(collect(), collect()), Collection::make([$token]));

        $token->refresh();
        $refreshToken->refresh();

        $this->assertTrue($token->revoked);
        $this->assertTrue($refreshToken->revoked);
    }
}
