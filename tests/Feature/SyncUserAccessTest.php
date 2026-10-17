<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\PushToJedi;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class SyncUserAccessTest extends TestCase
{
    public function test_sync_access_returns_accepted_and_dispatches_job_synchronously(): void
    {
        $user = User::factory()->create();

        Bus::fake();

        $this->actingAsClient(canReadUsers: true);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/'.$user->id.'/sync-access');

        $response->assertStatus(202);
        $response->assertExactJson([
            'status' => 'success',
            'message' => 'Access was synced successfully!',
        ]);

        Bus::assertDispatchedSync(PushToJedi::class);
    }

    public function test_sync_access_requires_authentication(): void
    {
        $user = User::factory()->create();

        Bus::fake();

        $response = $this->postJson('/api/v1/users/'.$user->id.'/sync-access');

        $response->assertStatus(401);

        Bus::assertNotDispatched(PushToJedi::class);
    }

    public function test_sync_access_rejects_user_access_tokens(): void
    {
        $user = User::factory()->create();
        $user->refresh();

        Bus::fake();

        Passport::actingAs($user);

        $response = $this->postJson('/api/v1/users/'.$user->id.'/sync-access');

        $response->assertStatus(401);

        Bus::assertNotDispatched(PushToJedi::class);
    }

    public function test_sync_access_requires_read_users_permission(): void
    {
        $user = User::factory()->create();

        Bus::fake();

        $this->actingAsClient(canReadUsers: false);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/'.$user->id.'/sync-access');

        $response->assertStatus(403);

        Bus::assertNotDispatched(PushToJedi::class);
    }

    public function test_sync_access_returns_not_found_for_unknown_user(): void
    {
        Bus::fake();

        $this->actingAsClient(canReadUsers: true);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/does-not-exist/sync-access');

        $response->assertStatus(404);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'User not found.',
        ]);

        Bus::assertNotDispatched(PushToJedi::class);
    }

    public function test_sync_access_rejects_service_accounts(): void
    {
        $user = User::factory()->create([
            'is_service_account' => true,
        ]);

        Bus::fake();

        $this->actingAsClient(canReadUsers: true);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/'.$user->id.'/sync-access');

        $response->assertStatus(422);
        $response->assertExactJson([
            'status' => 'error',
            'message' => 'Service accounts do not have access to other systems.',
        ]);

        Bus::assertNotDispatched(PushToJedi::class);
    }

    private function actingAsClient(bool $canReadUsers): void
    {
        $client = new ClientRepository()->createClientCredentialsGrantClient(name: 'test');

        if ($canReadUsers) {
            $client->givePermissionTo('read-users');
        }

        Passport::actingAsClient($client);
    }
}
