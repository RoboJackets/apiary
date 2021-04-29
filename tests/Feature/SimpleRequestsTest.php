<?php

namespace Tests\Feature;

use App\Models\User;
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
        $user = new User();
        $user->create_reason = 'phpunit';
        $user->is_service_account = false;
        $user->uid = 'apiarytesting4';
        $user->gtid = 901234567;
        $user->gt_email = 'robojackets-it@lists.gatech.edu';
        $user->first_name = 'Apiary';
        $user->last_name = 'PHPUnit';
        $user->primary_affiliation = 'student';
        $user->has_ever_logged_in = true;
        $user->save();

        $response = $this->actingAs($user, 'web')->get('/');
        $response->assertStatus(200);
        $response->dump();

        $response = $this->actingAs($user, 'web')->get('/');
        $response->assertStatus(200);
    }
}
