<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Device;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class DeviceControllerTest extends TestCase
{
    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->postJson('/api/v1/devices/inventory', []);

        $response->assertStatus(401);
    }

    public function test_user_without_create_attendance_cannot_inventory_device(): void
    {
        $user = $this->getTestUser(['non-member']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1234567',
                'hardware_version' => '1.0',
                'software_version' => '2.0',
                'firmware_version' => '3.0',
            ]);

        $response->assertStatus(403);
    }

    public function test_creates_device_with_last_seen_metadata(): void
    {
        $user = $this->getTestUser(['shared-device']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1234567',
                'hardware_version' => 'hw-1',
                'software_version' => 'sw-1',
                'firmware_version' => 'fw-1',
                'battery_percentage' => 75,
            ]);

        $response->assertStatus(201);
        $response->assertJson(static function (AssertableJson $json) use ($user): void {
            $json->where('status', 'success')
                ->has('device', static function (AssertableJson $json) use ($user): void {
                    $json->where('serial_number', 1234567)
                        ->where('hardware_version', 'hw-1')
                        ->where('software_version', 'sw-1')
                        ->where('firmware_version', 'fw-1')
                        ->where('battery_percentage', 75)
                        ->where('last_seen_user_id', (string) $user->id)
                        ->where('last_seen_ip_address', '192.168.1.50')
                        ->has('last_seen_at')
                        ->etc();
                });
        });

        $this->assertDatabaseHas('devices', [
            'serial_number' => 1234567,
            'hardware_version' => 'hw-1',
            'software_version' => 'sw-1',
            'firmware_version' => 'fw-1',
            'battery_percentage' => 75,
            'last_seen_user_id' => $user->id,
            'last_seen_ip_address' => '192.168.1.50',
        ]);
    }

    public function test_updates_existing_device_and_metadata(): void
    {
        $originalUser = $this->getTestUser(['non-member']);
        $device = Device::factory()->create([
            'serial_number' => 7654321,
            'hardware_version' => 'old-hw',
            'software_version' => 'old-sw',
            'firmware_version' => 'old-fw',
            'battery_percentage' => 88,
            'last_seen_user_id' => $originalUser->id,
            'last_seen_ip_address' => '10.0.0.1',
        ]);

        $user = $this->getTestUser(['shared-device'], 'apiarytestingshared');

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '7654321',
                'hardware_version' => 'new-hw',
                'software_version' => 'new-sw',
                'firmware_version' => 'new-fw',
            ]);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($user): void {
            $json->where('status', 'success')
                ->has('device', static function (AssertableJson $json) use ($user): void {
                    $json->where('serial_number', 7654321)
                        ->where('hardware_version', 'new-hw')
                        ->where('software_version', 'new-sw')
                        ->where('firmware_version', 'new-fw')
                        ->where('battery_percentage', 88)
                        ->where('last_seen_user_id', (string) $user->id)
                        ->where('last_seen_ip_address', '10.0.0.2')
                        ->etc();
                });
        });

        $device->refresh();
        $this->assertSame('new-hw', $device->hardware_version);
        $this->assertSame(88, $device->battery_percentage);
        $this->assertSame($user->id, $device->last_seen_user_id);
        $this->assertSame('10.0.0.2', $device->last_seen_ip_address);
    }

    public function test_explicit_null_battery_percentage_is_preserved(): void
    {
        $originalUser = $this->getTestUser(['non-member']);
        $device = Device::factory()->create([
            'serial_number' => 1122334,
            'hardware_version' => 'old-hw',
            'software_version' => 'old-sw',
            'firmware_version' => 'old-fw',
            'battery_percentage' => 42,
            'last_seen_user_id' => $originalUser->id,
            'last_seen_ip_address' => '10.0.0.1',
        ]);

        $user = $this->getTestUser(['shared-device'], 'apiarytestingshared2');

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1122334',
                'hardware_version' => 'new-hw',
                'software_version' => 'new-sw',
                'firmware_version' => 'new-fw',
                'battery_percentage' => null,
            ]);

        $response->assertStatus(200);

        $device->refresh();
        $this->assertSame(42, $device->battery_percentage);
    }

    public function test_invalid_serial_number_fails_validation(): void
    {
        $user = $this->getTestUser(['shared-device']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '12345',
                'hardware_version' => 'hw-1',
                'software_version' => 'sw-1',
                'firmware_version' => 'fw-1',
            ]);

        $response->assertStatus(422);
        $response->assertInvalid(['serial_number']);
    }

    public function test_missing_required_versions_fails_validation(): void
    {
        $user = $this->getTestUser(['shared-device']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1234567',
            ]);

        $response->assertStatus(422);
        $response->assertInvalid(['hardware_version', 'software_version', 'firmware_version']);
    }

    public function test_battery_percentage_out_of_range_fails_validation(): void
    {
        $user = $this->getTestUser(['shared-device']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1234567',
                'hardware_version' => 'hw-1',
                'software_version' => 'sw-1',
                'firmware_version' => 'fw-1',
                'battery_percentage' => 101,
            ]);

        $response->assertStatus(422);
        $response->assertInvalid(['battery_percentage']);
    }

    public function test_client_token_with_permission_is_rejected(): void
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test-device');
        $client->givePermissionTo('create-attendance');

        Passport::actingAsClient($client);

        $response = $this
            ->withToken('test')
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1234567',
                'hardware_version' => 'hw-1',
                'software_version' => 'sw-1',
                'firmware_version' => 'fw-1',
            ]);

        $response->assertStatus(401);
    }

    public function test_missing_request_ip_fails(): void
    {
        $user = $this->getTestUser(['shared-device']);

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => null])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/devices/inventory', [
                'serial_number' => '1234567',
                'hardware_version' => 'hw-1',
                'software_version' => 'sw-1',
                'firmware_version' => 'fw-1',
            ]);

        $response->assertStatus(422);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'last_seen_ip_address is required.');
        });
    }
}
