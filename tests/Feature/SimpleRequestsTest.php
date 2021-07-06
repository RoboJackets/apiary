<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class SimpleRequestsTest extends TestCase
{
    /**
     * Test simple, non-CAS requests load without any authentication.
     */
    public function testUnauthenticatedRequests(): void
    {
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
        $response = $this->actingAs($this->getTestUser(['member']), 'web')->get('/');
        $this->assertEquals(200, $response->status(), 'Response content: '.$response->getContent());

        $response = $this->actingAs($this->getTestUser(['non-member']), 'web')->get('/');
        $this->assertEquals(200, $response->status(), 'Response content: '.$response->getContent());
    }
}
