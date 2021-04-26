<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SimpleRequestsTest extends TestCase
{
    use RefreshDatabase;

    /**
	 * Test simple, non-CAS requests work.
     */
    public function test_unauthenticatedRequests(): void
    {
        $response = $this->get('/privacy');
        $response->assertStatus(200);

		$response = $this->get('/attendance/kiosk');
        $response->assertStatus(200);
    }

    /**
	 * Test that the home page works.
     */
    public function test_home(): void
    {
        $response = $this->get('/');
        print_r($response);
        $response->assertStatus(200);
    }
}
