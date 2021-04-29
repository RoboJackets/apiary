<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test simple, non-CAS requests work.
     */
    public function testUnauthenticatedRequests(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);

        $response = $this->get('/attendance/kiosk');
        $response->assertStatus(200);
    }

    /**
     * Test that the home page works.
     */
    public function testHome(): void
    {
        $response = $this->actingAs($this->getTestUser(['member']), 'web')->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test API auth.
     */
    public function testApiAuth(): void
    {
        $response = $this->actingAs($this->getTestUser(['member']), 'web')->get('/api/v1/users/1?include=roles');
        // FIXME $response->dump();
        $response->assertStatus(200);

        $response = $this->actingAs($this->getTestUser(['member', 'admin']), 'web')->get('/api/v1/users/1?include=roles');
        // FIXME $response->dump();
        $response->assertStatus(200);
    }
}
