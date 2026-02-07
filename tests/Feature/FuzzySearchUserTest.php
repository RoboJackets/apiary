<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class FuzzySearchUserTest extends TestCase
{
    public function test_fuzzy_search_returns_matching_users(): void
    {
        $user = $this->getTestUser(['admin']);

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo('read-users');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/fuzzySearch', ['query' => $user->first_name]);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($user): void {
            $json->where('status', 'success')
                ->has('users')
                ->has('users.0', static function (AssertableJson $json) use ($user): void {
                    $json->where('id', $user->id)
                        ->etc();
                });
        });
    }

    public function test_fuzzy_search_returns_empty_for_no_match(): void
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo('read-users');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/fuzzySearch', ['query' => 'zzzznonexistentuserzzz']);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->where('users', []);
        });
    }

    public function test_fuzzy_search_fails_validation_when_query_missing(): void
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo('read-users');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/fuzzySearch', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('query');
    }

    public function test_fuzzy_search_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/users/fuzzySearch', ['query' => 'test']);

        $response->assertStatus(401);
    }

    public function test_fuzzy_search_requires_read_users_permission(): void
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test-no-perms');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->postJson('/api/v1/users/fuzzySearch', ['query' => 'test']);

        $response->assertStatus(403);
    }
}
