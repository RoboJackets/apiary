<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Major;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class MajorControllerTest extends TestCase
{
    public function test_majors_index_returns_all_majors(): void
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test');

        Major::create([
            'display_name' => 'Computer Science',
            'gtad_majorgroup_name' => 'coc_cs',
            'whitepages_ou' => 'COC',
            'school' => 'CS',
        ]);

        Major::create([
            'display_name' => 'Mechanical Engineering',
            'gtad_majorgroup_name' => 'coe_me',
            'whitepages_ou' => 'COE',
            'school' => 'ME',
        ]);

        Passport::actingAsClient($client);

        $response = $this->getJson('/api/v1/majors');

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'success')
                ->has('majors', 2)
                ->has('majors.0', static function (AssertableJson $json): void {
                    $json->has('id')
                        ->has('display_name')
                        ->has('gtad_majorgroup_name')
                        ->has('whitepages_ou')
                        ->has('school')
                        ->has('created_at')
                        ->has('updated_at');
                });
        });
    }

    public function test_majors_index_requires_client_authentication(): void
    {
        Major::create([
            'display_name' => 'Computer Science',
            'gtad_majorgroup_name' => 'coc_cs',
            'whitepages_ou' => 'COC',
            'school' => 'CS',
        ]);

        $response = $this->getJson('/api/v1/majors');

        $response->assertStatus(401);
    }
}
