<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersSeeder;
use Illuminate\Testing\Fluent\AssertableJson;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApiAuthTest extends TestCase
{
    /**
     * Test API auth.
     */
    public function test_users_api_auth(): void
    {
        $testId = $this->getTestUser(['member'])->id;
        $this->seed(UsersSeeder::class);
        $alternateUser = User::where('id', '!=', $testId)->first();
        $alternateId = $alternateUser->id;
        $alternateUser->syncRoles(['member']);

        $memberPerms = Role::findByName('member')->permissions->pluck('name')->sort()->values();
        $adminPerms = Role::findByName('admin')->permissions->pluck('name')->sort()->values();
        $this->assertCount(count($memberPerms), User::find($testId)->getAllPermissions());

        // Same user, read-users-own
        $response = $this->actingAs($this->getTestUser(['member']), 'api')
            ->get('/api/v1/users/'.$testId.'?include=roles,permissions');
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($testId): void {
            $json->where('status', 'success')
                ->has('user', static function (AssertableJson $json) use ($testId): void {
                    $json->where('id', $testId)
                        ->missing('allPermissions')
                        ->missing('roles')
                        ->missing('permissions')
                        ->etc();
                })
                ->etc();
        });

        // Same user, read-users-own, base route
        $response = $this->actingAs($this->getTestUser(['member']), 'api')
            ->get('/api/v1/user');
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($testId, $memberPerms): void {
            $json->where('status', 'success')
                ->has('user', static function (AssertableJson $json) use ($testId, $memberPerms): void {
                    $json->where('id', $testId)
                        ->where(
                            'allPermissions',
                            static fn ($perms) => collect($perms)->sort()->values()->all() === $memberPerms->all()
                        )
                        ->has('roles')
                        ->has('permissions')
                        ->missing('teams')
                        ->missing('events')
                        ->missing('attendance')
                        ->missing('dues')
                        ->etc();
                })
                ->etc();
        });

        // Same user, with admin
        $response = $this->actingAs($this->getTestUser(['member', 'admin']), 'api')
            ->get('/api/v1/users/'.$testId.'?include=roles,permissions');
        $response->assertJson(static function (AssertableJson $json) use ($testId, $adminPerms): void {
            $json->where('status', 'success')
                ->has('user', static function (AssertableJson $json) use ($testId, $adminPerms): void {
                    $json->where('id', $testId)
                        ->where(
                            'allPermissions',
                            static fn ($perms) => collect($perms)->sort()->values()->all() === $adminPerms->all()
                        )
                        ->has('roles')
                        ->has('permissions')
                        ->etc();
                })
                ->etc();
        });
        $response->assertStatus(200);

        // Different user, no permissions
        $response = $this->actingAs($this->getTestUser(['member']), 'api')
            ->get('/api/v1/users/'.$alternateId);
        $response->assertStatus(403);

        // Different user, with permissions
        $response = $this->actingAs($this->getTestUser(['member', 'admin']), 'api')
            ->get('/api/v1/users/'.$alternateId.'?include=roles,permissions');
        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($alternateId, $memberPerms): void {
            $json->where('status', 'success')
                ->has('user', static function (AssertableJson $json) use ($alternateId, $memberPerms): void {
                    $json->where('id', $alternateId)
                        ->where(
                            'allPermissions',
                            static fn ($perms) => collect($perms)->sort()->values()->all() === $memberPerms->all()
                        )
                        ->has('roles')
                        ->has('permissions')
                        ->etc();
                })
                ->etc();
        });
    }
}
