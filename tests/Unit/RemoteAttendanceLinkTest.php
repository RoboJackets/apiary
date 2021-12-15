<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\RemoteAttendanceLink;
use PHPUnit\Framework\TestCase;

class RemoteAttendanceLinkTest extends TestCase
{
    /**
     * Test random links.
     */
    public function testRedirectRegexRandom(): void
    {
        $this->redirectRegexTestCase('google.com', false);
    }

    /**
     * Test BlueJeans links.
     */
    public function testRedirectRegexBlueJeans(): void
    {
        $this->redirectRegexTestCase('bluejeans.com/01234', true);
        $this->redirectRegexTestCase('bluejeans.com/01234/01234', true);
        $this->redirectRegexTestCase('bluejeans.com/01234?querystring', true);
        $this->redirectRegexTestCase('bluejeans.com/01234/01234?querystring', true);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234', true);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234/01234', true);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234?querystring', true);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234/01234?querystring', true);
        $this->redirectRegexTestCase('primetime.bluejeans.com/a2m/live-event/abcd', true);
        $this->redirectRegexTestCase('primetime.bluejeans.com/a2m/live-event/abcd?querystring', true);

        $this->redirectRegexTestCase('bluejeans.com/abcd', false);
        $this->redirectRegexTestCase('bluejeans.com/abcd/01234', false);
        $this->redirectRegexTestCase('bluejeans.com/01234/abcd', false);
        $this->redirectRegexTestCase('gatech.bluejeans.com/abcd', false);
        $this->redirectRegexTestCase('gatech.bluejeans.com/abcd/01234', false);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234/abcd', false);
        $this->redirectRegexTestCase('primetime.bluejeans.com/a2m/live-event/0123', false);

        $this->redirectRegexTestCase('bluejeans.com/01234?query@query', false);
        $this->redirectRegexTestCase('bluejeans.com/01234/01234?query@query', false);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234?query@query', false);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234/01234?query@query', false);
        $this->redirectRegexTestCase('primetime.bluejeans.com/a2m/live-event/abcd?query@query', false);

        $this->redirectRegexTestCase('bluejeans.com/01234', true);
        $this->redirectRegexTestCase('bluejeans.com/01234/01234', true);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234', true);
        $this->redirectRegexTestCase('gatech.bluejeans.com/01234/01234', true);
        $this->redirectRegexTestCase('primetime.bluejeans.com/a2m/live-event/abcd', true);

        $this->redirectRegexTestCase('bluejeans.com/', false);
    }

    /**
     * Test Microsoft Teams links.
     */
    public function testRedirectRegexMicrosoftTeams(): void
    {
        $this->redirectRegexTestCase('teams.microsoft.com/l/meetup-join/abcd-%.0123/01234', true);
        $this->redirectRegexTestCase('teams.microsoft.com/l/meetup-join/abcd-%.0123/01234?querystring', true);

        $this->redirectRegexTestCase('teams.microsoft.com/l/meetup-join/abcd-%.0123/abcd', false);
        $this->redirectRegexTestCase('teams.microsoft.com/l/meetup-join/abcd-%.0123/01234', true);
        $this->redirectRegexTestCase('teams.microsoft.com/l/meetup-join/abcd-%.0123/01234?query@query', false);
        $this->redirectRegexTestCase('teams.microsoft.com/l/meetup-join/', false);
    }

    /**
     * Test Google Meet links.
     */
    public function testRedirectRegexGoogleMeet(): void
    {
        $this->redirectRegexTestCase('meet.google.com/aaa-aaaa-aaa', true);
        $this->redirectRegexTestCase('meet.google.com/aaa-aaaa-aaa?querystring', true);

        $this->redirectRegexTestCase('meet.google.com/', false);
        $this->redirectRegexTestCase('meet.google.com/000-0000-000', false);
        $this->redirectRegexTestCase('meet.google.com/0a0-a0a0-a0a', false);
        $this->redirectRegexTestCase('meet.google.com/aaa-aaaa-aaa', true);
        $this->redirectRegexTestCase('meet.google.com/aaa-aaaa-aaa?query@query', false);
    }

    private function redirectRegexTestCase(string $url, bool $expected): void
    {
        if ($expected) {
            $this->assertMatchesRegularExpression(RemoteAttendanceLink::$redirectRegex, $url);
            $this->assertMatchesRegularExpression(RemoteAttendanceLink::$redirectRegex, 'http://'.$url);
            $this->assertMatchesRegularExpression(RemoteAttendanceLink::$redirectRegex, 'https://'.$url);
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, 'ftp://'.$url);

            $normalized = RemoteAttendanceLink::normalizeRedirectUrl($url);
            $this->assertStringStartsWith('https://', $normalized);
            $this->assertThat(
                $normalized,
                $this->logicalNot($this->stringContains('http://'))
            );
        } else {
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, $url);
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, 'http://'.$url);
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, 'https://'.$url);

            // Ensure it doesn't somehow get fixed by the normalize function.
            $normalized = RemoteAttendanceLink::normalizeRedirectUrl($url);
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, $url);
        }
    }
}
