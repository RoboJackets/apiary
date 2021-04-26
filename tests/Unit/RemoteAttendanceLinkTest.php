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
    public function test_redirectRegexRandom(): void
    {
        $this->_redirectRegexTestCase('https://google.com', false);
    }

    /**
     * Test BlueJeans links.
     */
    public function test_redirectRegexBlueJeans(): void
    {
        $this->_redirectRegexTestCase('https://bluejeans.com/01234', true);
        $this->_redirectRegexTestCase('https://bluejeans.com/01234/01234', true);
        $this->_redirectRegexTestCase('https://bluejeans.com/01234?querystring', true);
        $this->_redirectRegexTestCase('https://bluejeans.com/01234/01234?querystring', true);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234', true);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234/01234', true);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234?querystring', true);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234/01234?querystring', true);
        $this->_redirectRegexTestCase('https://primetime.bluejeans.com/a2m/live-event/abcd', true);
        $this->_redirectRegexTestCase('https://primetime.bluejeans.com/a2m/live-event/abcd?querystring', true);

        $this->_redirectRegexTestCase('https://bluejeans.com/abcd', false);
        $this->_redirectRegexTestCase('https://bluejeans.com/abcd/01234', false);
        $this->_redirectRegexTestCase('https://bluejeans.com/01234/abcd', false);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/abcd', false);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/abcd/01234', false);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234/abcd', false);
        $this->_redirectRegexTestCase('https://primetime.bluejeans.com/a2m/live-event/0123', false);

        $this->_redirectRegexTestCase('https://bluejeans.com/01234?query@query', false);
        $this->_redirectRegexTestCase('https://bluejeans.com/01234/01234?query@query', false);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234?query@query', false);
        $this->_redirectRegexTestCase('https://gatech.bluejeans.com/01234/01234?query@query', false);
        $this->_redirectRegexTestCase('https://primetime.bluejeans.com/a2m/live-event/abcd?query@query', false);

        $this->_redirectRegexTestCase('http://bluejeans.com/01234', false);
        $this->_redirectRegexTestCase('http://bluejeans.com/01234/01234', false);
        $this->_redirectRegexTestCase('http://gatech.bluejeans.com/01234', false);
        $this->_redirectRegexTestCase('http://gatech.bluejeans.com/01234/01234', false);
        $this->_redirectRegexTestCase('http://primetime.bluejeans.com/a2m/live-event/abcd', false);

        $this->_redirectRegexTestCase('https://bluejeans.com/', false);
    }

    /**
     * Test Microsoft Teams links.
     */
    public function test_redirectRegexMicrosoftTeams(): void
    {
        $this->_redirectRegexTestCase('https://teams.microsoft.com/l/meetup-join/abcd-%.0123/01234', true);
        $this->_redirectRegexTestCase('https://teams.microsoft.com/l/meetup-join/abcd-%.0123/01234?querystring', true);

        $this->_redirectRegexTestCase('https://teams.microsoft.com/l/meetup-join/abcd-%.0123/abcd', false);
        $this->_redirectRegexTestCase('http://teams.microsoft.com/l/meetup-join/abcd-%.0123/01234', false);
        $this->_redirectRegexTestCase('https://teams.microsoft.com/l/meetup-join/abcd-%.0123/01234?query@query', false);
        $this->_redirectRegexTestCase('https://teams.microsoft.com/l/meetup-join/', false);
    }

    /**
     * Test Google Meet links.
     */
    public function test_redirectRegexGoogleMeet(): void
    {
        $this->_redirectRegexTestCase('https://meet.google.com/aaa-aaaa-aaa', true);
        $this->_redirectRegexTestCase('https://meet.google.com/aaa-aaaa-aaa?querystring', true);

        $this->_redirectRegexTestCase('https://meet.google.com/', false);
        $this->_redirectRegexTestCase('https://meet.google.com/000-0000-000', false);
        $this->_redirectRegexTestCase('https://meet.google.com/0a0-a0a0-a0a', false);
        $this->_redirectRegexTestCase('http://meet.google.com/aaa-aaaa-aaa', false);
        $this->_redirectRegexTestCase('https://meet.google.com/aaa-aaaa-aaa?query@query', false);
    }

    private function _redirectRegexTestCase(string $url, bool $expected): void
    {
        if ($expected) {
            $this->assertMatchesRegularExpression(RemoteAttendanceLink::$redirectRegex, $url);
        } else {
            $this->assertDoesNotMatchRegularExpression(RemoteAttendanceLink::$redirectRegex, $url);
        }
    }
}
