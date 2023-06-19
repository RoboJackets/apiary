<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Mail\ExpiringPersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

final class ExpiringPersonalAccessTokenEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $clientRepository = new ClientRepository();

        $client = $clientRepository->createPersonalAccessClient(null, 'test', 'http://localhost');

        Config::set('passport.personal_access_client.id', $client->id);
        Config::set('passport.personal_access_client.secret', $client->plain_secret);
    }

    public function testGenerateEmailForAlreadyExpiredToken(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('email test token')->token;
        $token->expires_at = now()->subHours(1);
        $token->save();

        $mailable = new ExpiringPersonalAccessToken($token);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText('expired on');
        $mailable->assertSeeInText($token->name);
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }

    public function testGenerateEmailForTokenExpiringSoon(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('email test token')->token;
        $token->expires_at = now()->addHours(1);
        $token->save();

        $mailable = new ExpiringPersonalAccessToken($token);

        $mailable->assertSeeInText($user->preferred_first_name);
        $mailable->assertSeeInText('will expire on');
        $mailable->assertSeeInText($token->name);
        $mailable->assertSeeInText('{{{ pm:unsubscribe }}}');
        $mailable->assertDontSeeInText("\n\n\n");
    }
}
