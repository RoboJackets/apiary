<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Team;
use Database\Seeders\TeamsSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

final class AttendanceControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->seed(TeamsSeeder::class);
    }

    public function test_creates_attendance_without_reader(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
            ]);

        $response->assertStatus(201);
        $response->assertJson(static function (AssertableJson $json) use ($team): void {
            $json->where('status', 'success')
                ->has('attendance', static function (AssertableJson $json) use ($team): void {
                    $json->where('attendable_type', 'team')
                        ->where('attendable_id', $team->id)
                        ->where('source', 'kiosk')
                        ->where('device_serial_number', null)
                        ->etc();
                });
        });

        $this->assertDatabaseHas('attendance', [
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
            'source' => 'kiosk',
            'recorded_by' => $user->id,
            'device_serial_number' => null,
        ]);
    }

    public function test_creates_attendance_with_reader_and_links_device(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '1234567',
                    'manufacturer' => 'mfg-1',
                    'model' => 'mdl-1',
                    'hardware_version' => 'hw-1',
                    'bluetooth_firmware_version' => 'bt-fw-1',
                    'bluetooth_software_version' => 'bt-sw-1',
                    'bootloader_version' => 'bld-1',
                    'application_version' => 'app-1',
                    'battery_percentage' => 75,
                ],
            ]);

        $response->assertStatus(201);
        $response->assertJson(static function (AssertableJson $json) use ($team): void {
            $json->where('status', 'success')
                ->has('attendance', static function (AssertableJson $json) use ($team): void {
                    $json->where('attendable_type', 'team')
                        ->where('attendable_id', $team->id)
                        ->where('source', 'kiosk')
                        ->where('device_serial_number', 1234567)
                        ->etc();
                });
        });

        $this->assertDatabaseHas('attendance', [
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
            'device_serial_number' => 1234567,
        ]);

        $this->assertDatabaseHas('devices', [
            'serial_number' => 1234567,
            'manufacturer' => 'mfg-1',
            'model' => 'mdl-1',
            'hardware_version' => 'hw-1',
            'bluetooth_firmware_version' => 'bt-fw-1',
            'bluetooth_software_version' => 'bt-sw-1',
            'bootloader_version' => 'bld-1',
            'application_version' => 'app-1',
            'battery_percentage' => 75,
            'last_seen_user_id' => $user->id,
            'last_seen_ip_address' => '192.168.1.50',
        ]);
    }

    public function test_updates_existing_attendance_device_metadata_but_not_link(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $this
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '1111111',
                    'manufacturer' => 'old-mfg',
                    'model' => 'old-mdl',
                    'hardware_version' => 'old-hw',
                    'bluetooth_firmware_version' => 'old-bt-fw',
                    'bluetooth_software_version' => 'old-bt-sw',
                    'bootloader_version' => 'old-bld',
                    'application_version' => 'old-app',
                    'battery_percentage' => 10,
                ],
            ]);

        $this
            ->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '2222222',
                    'manufacturer' => 'new-mfg',
                    'model' => 'new-mdl',
                    'hardware_version' => 'new-hw',
                    'bluetooth_firmware_version' => 'new-bt-fw',
                    'bluetooth_software_version' => 'new-bt-sw',
                    'bootloader_version' => 'new-bld',
                    'application_version' => 'new-app',
                    'battery_percentage' => 90,
                ],
            ]);

        $this->assertDatabaseHas('attendance', [
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
            'device_serial_number' => 1111111,
        ]);

        $this->assertDatabaseHas('devices', [
            'serial_number' => 2222222,
            'manufacturer' => 'new-mfg',
            'model' => 'new-mdl',
            'hardware_version' => 'new-hw',
            'bluetooth_firmware_version' => 'new-bt-fw',
            'bluetooth_software_version' => 'new-bt-sw',
            'bootloader_version' => 'new-bld',
            'application_version' => 'new-app',
            'battery_percentage' => 90,
            'last_seen_user_id' => $user->id,
            'last_seen_ip_address' => '10.0.0.2',
        ]);

        $this->assertDatabaseMissing('attendance', [
            'attendable_type' => 'team',
            'attendable_id' => $team->id,
            'gtid' => $user->gtid,
            'device_serial_number' => 2222222,
        ]);
    }

    public function test_client_token_with_reader_is_rejected(): void
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createClientCredentialsGrantClient(name: 'test-device');
        $client->givePermissionTo('create-attendance');

        Passport::actingAsClient($client);

        $team = Team::first();

        $response = $this
            ->withToken('test')
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => 901123456,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '1234567',
                    'manufacturer' => 'mfg-1',
                    'model' => 'mdl-1',
                    'hardware_version' => 'hw-1',
                    'bluetooth_firmware_version' => 'bt-fw-1',
                    'bluetooth_software_version' => 'bt-sw-1',
                    'bootloader_version' => 'bld-1',
                    'application_version' => 'app-1',
                    'battery_percentage' => 75,
                ],
            ]);

        $response->assertStatus(401);
    }

    public function test_missing_reader_battery_fails_validation(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '1234567',
                    'manufacturer' => 'mfg-1',
                    'model' => 'mdl-1',
                    'hardware_version' => 'hw-1',
                    'bluetooth_firmware_version' => 'bt-fw-1',
                    'bluetooth_software_version' => 'bt-sw-1',
                    'bootloader_version' => 'bld-1',
                    'application_version' => 'app-1',
                ],
            ]);

        $response->assertStatus(422);
        $response->assertInvalid(['reader.battery_percentage']);
    }

    public function test_reader_battery_out_of_range_fails_validation(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '1234567',
                    'manufacturer' => 'mfg-1',
                    'model' => 'mdl-1',
                    'hardware_version' => 'hw-1',
                    'bluetooth_firmware_version' => 'bt-fw-1',
                    'bluetooth_software_version' => 'bt-sw-1',
                    'bootloader_version' => 'bld-1',
                    'application_version' => 'app-1',
                    'battery_percentage' => 101,
                ],
            ]);

        $response->assertStatus(422);
        $response->assertInvalid(['reader.battery_percentage']);
    }

    public function test_missing_request_ip_with_reader_fails(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => null])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '1234567',
                    'manufacturer' => 'mfg-1',
                    'model' => 'mdl-1',
                    'hardware_version' => 'hw-1',
                    'bluetooth_firmware_version' => 'bt-fw-1',
                    'bluetooth_software_version' => 'bt-sw-1',
                    'bootloader_version' => 'bld-1',
                    'application_version' => 'app-1',
                    'battery_percentage' => 75,
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJson(static function (AssertableJson $json): void {
            $json->where('status', 'error')
                ->where('message', 'last_seen_ip_address is required.');
        });
    }

    public function test_device_is_included_when_requested(): void
    {
        $user = $this->getTestUser(['shared-device']);
        $team = Team::first();

        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.50'])
            ->actingAs($user, 'api')
            ->postJson('/api/v1/attendance?include=device', [
                'attendable_type' => 'team',
                'attendable_id' => $team->id,
                'gtid' => $user->gtid,
                'source' => 'kiosk',
                'reader' => [
                    'serial_number' => '7654321',
                    'manufacturer' => 'mfg-1',
                    'model' => 'mdl-1',
                    'hardware_version' => 'hw-1',
                    'bluetooth_firmware_version' => 'bt-fw-1',
                    'bluetooth_software_version' => 'bt-sw-1',
                    'bootloader_version' => 'bld-1',
                    'application_version' => 'app-1',
                    'battery_percentage' => 42,
                ],
            ]);

        $response->assertStatus(201);
        $response->assertJson(static function (AssertableJson $json) use ($user): void {
            $json->where('status', 'success')
                ->has('attendance', static function (AssertableJson $json) use ($user): void {
                    $json->where('device_serial_number', 7654321)
                        ->has('device', static function (AssertableJson $json) use ($user): void {
                            $json->where('serial_number', 7654321)
                                ->where('manufacturer', 'mfg-1')
                                ->where('model', 'mdl-1')
                                ->where('hardware_version', 'hw-1')
                                ->where('bluetooth_firmware_version', 'bt-fw-1')
                                ->where('bluetooth_software_version', 'bt-sw-1')
                                ->where('bootloader_version', 'bld-1')
                                ->where('application_version', 'app-1')
                                ->where('battery_percentage', 42)
                                ->where('last_seen_user_id', $user->id)
                                ->etc();
                        })
                        ->etc();
                });
        });
    }
}
