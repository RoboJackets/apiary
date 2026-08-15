<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class TeamControllerTest extends TestCase
{
    public function test_show_returns_visible_team_for_client_without_hidden_permission(): void
    {
        $team = Team::create([
            'name' => 'Visible Team',
            'visible' => true,
            'attendable' => false,
            'self_serviceable' => false,
            'visible_on_kiosk' => false,
            'self_service_override_eligible' => false,
        ]);

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo('read-teams');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->getJson('/api/v1/teams/'.$team->id);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($team): void {
            $json->where('status', 'success')
                ->where('team.id', $team->id)
                ->etc();
        });
    }

    public function test_show_hides_invisible_team_from_client_without_hidden_permission(): void
    {
        $team = Team::create([
            'name' => 'Hidden Team',
            'visible' => false,
            'attendable' => false,
            'self_serviceable' => false,
            'visible_on_kiosk' => false,
            'self_service_override_eligible' => false,
        ]);

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo('read-teams');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->getJson('/api/v1/teams/'.$team->id);

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'message' => 'team_not_found',
        ]);
    }

    public function test_show_returns_invisible_team_for_client_with_hidden_permission(): void
    {
        $team = Team::create([
            'name' => 'Hidden Team With Access',
            'visible' => false,
            'attendable' => false,
            'self_serviceable' => false,
            'visible_on_kiosk' => false,
            'self_service_override_eligible' => false,
        ]);

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo(['read-teams', 'read-teams-hidden']);

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->getJson('/api/v1/teams/'.$team->id);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($team): void {
            $json->where('status', 'success')
                ->where('team.id', $team->id)
                ->etc();
        });
    }

    public function test_show_members_hides_invisible_team_from_client_without_hidden_permission(): void
    {
        $team = Team::create([
            'name' => 'Hidden Team Members',
            'visible' => false,
            'attendable' => false,
            'self_serviceable' => false,
            'visible_on_kiosk' => false,
            'self_service_override_eligible' => false,
        ]);

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');
        $client->givePermissionTo('read-teams');

        Passport::actingAsClient($client);

        $response = $this->withToken('test')
            ->getJson('/api/v1/teams/'.$team->id.'/members');

        $response->assertStatus(404);
        $response->assertJson([
            'status' => 'error',
            'message' => 'team_not_found',
        ]);
    }
}
