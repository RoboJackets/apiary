<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleRequestsTest extends TestCase
{
    use RefreshDatabase;

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
        $response->assertStatus(200);

        $response = $this->actingAs($this->getTestUser(['non-member']), 'web')->get('/');
        $response->assertStatus(200);
    }
}
