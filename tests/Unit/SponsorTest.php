<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Notifications\SponsorOneTimePasswordNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\OneTimePasswords\Enums\ConsumeOneTimePasswordResult;
use Tests\TestCase;

final class SponsorTest extends TestCase
{
    public function test_one_time_password_login(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $code = $user->createOneTimePassword()->password;

        $result = $user->attemptLoginUsingOneTimePassword($code, remember: false);

        $this->assertTrue($result->isOk());
        $this->assertAuthenticatedAs($user);
    }

    public function test_consume_without_login(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $code = $user->createOneTimePassword()->password;

        $result = $user->consumeOneTimePassword($code);

        $this->assertSame(ConsumeOneTimePasswordResult::Ok, $result);
        $this->assertGuest();
    }

    public function test_one_time_password_expiry(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $code = $user->createOneTimePassword()->password;

        $this->travel(10)->minutes();

        $result = $user->consumeOneTimePassword($code);

        $this->assertSame(ConsumeOneTimePasswordResult::OneTimePasswordExpired, $result);
    }

    public function test_code_is_single_use(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $code = $user->createOneTimePassword()->password;

        $first = $user->consumeOneTimePassword($code);
        $second = $user->consumeOneTimePassword($code);

        $this->assertSame(ConsumeOneTimePasswordResult::Ok, $first);
        $this->assertSame(ConsumeOneTimePasswordResult::NoOneTimePasswordsFound, $second);
    }

    public function test_wrong_code_is_rejected(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $user->createOneTimePassword();

        $result = $user->attemptLoginUsingOneTimePassword('000000', remember: false);

        $this->assertSame(ConsumeOneTimePasswordResult::IncorrectOneTimePassword, $result);
        $this->assertGuest();
    }

    public function test_user_with_no_code_is_rejected(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $result = $user->consumeOneTimePassword('123456');

        $this->assertSame(ConsumeOneTimePasswordResult::NoOneTimePasswordsFound, $result);
    }

    public function test_correct_notification_sent(): void
    {
        Notification::fake();
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $user->sendOneTimePassword();

        Notification::assertSentToTimes($user, SponsorOneTimePasswordNotification::class, 1);
        $notification = Notification::sent($user, SponsorOneTimePasswordNotification::class)->first();
        $code = $notification->oneTimePassword->password;
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertTrue($user->consumeOneTimePassword($code)->isOk());
    }

    public function test_rate_limit(): void
    {
        $sponsor = $this->getTestSponsor();
        $this->getTestSponsorDomain($sponsor);
        $user = $this->getTestSponsorUser($sponsor);

        $user->createOneTimePassword();

        // default: 5 attempts per user per 60 seconds
        for ($i = 0; $i < 5; $i++) {
            $user->consumeOneTimePassword('000000');
        }

        $this->assertSame(
            ConsumeOneTimePasswordResult::RateLimitExceeded,
            $user->consumeOneTimePassword('000000')
        );
    }
}
