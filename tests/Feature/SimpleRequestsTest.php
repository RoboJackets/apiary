<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class SimpleRequestsTest extends TestCase
{
    /**
     * Test simple, non-CAS requests load without any authentication.
     */
    public function testUnauthenticatedRequests(): void
    {
        $this->withoutMix();

        $response = $this->get('/privacy');
        $response->assertStatus(200);

        $response = $this->get('/attendance/kiosk');
        $response->assertStatus(200);
    }

    /**
     * Test that the home page loads successfully.
     */
    public function testHome(): void
    {
        $this->withoutMix();

        $response = $this->actingAs($this->getTestUser(['member']), 'web')->get('/');
        $this->assertEquals(200, $response->status(), 'Response content: '.$response->getContent());

        $response = $this->actingAs($this->getTestUser(['non-member']), 'web')->get('/');
        $this->assertEquals(200, $response->status(), 'Response content: '.$response->getContent());
    }

    /**
     * Test the info endpoint.
     */
    public function testInfo(): void
    {
        $response = $this->get('/api/v1/info');
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->has('info', static function (AssertableJson $json): void {
                    $json->where('appName', 'TESTING Apiary')
                        ->where('appEnv', 'testing')
                        ->where('allocId', 'asdf')
                        ->where('release', 'jkl');
                });
        });
    }
}
