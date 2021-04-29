<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test API auth.
     */
    public function testUsersApiAuth(): void
    {
        $testId = $this->getTestUser(['member'])->id;
        $this->seed(UsersSeeder::class);
        $alternateUser = User::where('id', '!=', $testId)->first();
        $alternateId = $alternateUser->id;

        // Same user, read-users-own
        $response = $this->actingAs($this->getTestUser(['member']), 'web')
                         ->get('/api/v1/users/'.$testId.'?include=roles');
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($testId): void {
            $json->where('status', 'success')
                 ->has('user', static function (AssertableJson $json) use ($testId): void {
                     $json->where('id', $testId)
                          ->missing('roles') // Missing roles since they can only see this because it's themself.
                          ->etc();
                 })
                 ->etc();
        });

        // Same user, with admin
        $response = $this->actingAs($this->getTestUser(['member', 'admin']), 'web')
                         ->get('/api/v1/users/'.$testId.'?include=roles');
        $response->assertJson(static function (AssertableJson $json) use ($testId): void {
            $json->where('status', 'success')
                 ->has('user', static function (AssertableJson $json) use ($testId): void {
                     $json->where('id', $testId)
                          ->has('roles') // Has roles
                          ->etc();
                 })
                 ->etc();
        });
        $response->assertStatus(200);

        // Different user, no permissions
        $response = $this->actingAs($this->getTestUser(['member']), 'web')
                         ->get('/api/v1/users/'.$alternateId);
        $response->assertStatus(403);

        // Different user, with permissions
        $response = $this->actingAs($this->getTestUser(['member', 'admin']), 'web')
                         ->get('/api/v1/users/'.$alternateId.'?include=roles');
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($alternateId): void {
            $json->where('status', 'success')
                 ->has('user', static function (AssertableJson $json) use ($alternateId): void {
                     $json->where('id', $alternateId)
                          ->has('roles') // Has roles
                          ->etc();
                 })
                 ->etc();
        });
    }
}
