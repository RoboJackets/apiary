<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\RemoteAttendanceLink;
use PHPUnit\Framework\TestCase;

final class RemoteAttendanceLinkTest extends TestCase
{
    /**
     * Test random links.
     */
    public function testRedirectRegexRandom(): void
    {
        $this->redirectRegexTestCase('google.com', false);
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

    /**
     * Test Zoom links.
     */
    public function testRedirectRegexZoom(): void
    {
        $this->redirectRegexTestCase('gatech.zoom.us/j/12345678901?pwd=aBCdE1fghI2JKlMNOPQrSTuVWxYz3456', true);
        $this->redirectRegexTestCase('gatech.zoom.us/j/12345678901', true);

        $this->redirectRegexTestCase('zoom.us/j/12345678901?pwd=aBCdE1fghI2JKlMNOPQrSTuVWxYz3456', false);
        $this->redirectRegexTestCase('zoom.us/j/12345678901', false);
        $this->redirectRegexTestCase('uga.zoom.us/j/12345678901?pwd=aBCdE1fghI2JKlMNOPQrSTuVWxYz3456', false);
        $this->redirectRegexTestCase('uga.zoom.us/j/12345678901', false);
        $this->redirectRegexTestCase('gatech.zoom.us/j/?pwd=aBCdE1fghI2JKlMNOPQrSTuVWxYz3456', false);
        $this->redirectRegexTestCase('gatech.zoom.us/j/abcdefg', false);
        $this->redirectRegexTestCase('gatech.zoom.us/j/12345?query@query', false);
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
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, $normalized);
        }
    }
}
