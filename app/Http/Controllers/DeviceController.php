<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Resources\Device as DeviceResource;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DeviceController implements HasMiddleware
{
    #[\Override]
    public static function middleware(): array
    {
        return [
            new Middleware('permission:create-attendance', only: ['inventory']),
        ];
    }

    /**
     * Create or update a Device from an inventory report.
     */
    public function inventory(StoreDeviceRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'status' => 'error',
                'message' => 'A user token is required.',
            ], 401);
        }

        $ipAddress = $request->ip();

        if ($ipAddress === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'last_seen_ip_address is required.',
            ], 422);
        }

        $validated = $request->validated();

        if (array_key_exists('battery_percentage', $validated) && $validated['battery_percentage'] === null) {
            unset($validated['battery_percentage']);
        }

        $device = Device::updateOrCreate(
            ['serial_number' => $validated['serial_number']],
            array_merge(
                $validated,
                [
                    'last_seen_user_id' => $user->id,
                    'last_seen_at' => now(),
                    'last_seen_ip_address' => $ipAddress,
                ]
            )
        );

        $code = $device->wasRecentlyCreated ? 201 : 200;

        return response()->json(
            [
                'status' => 'success',
                'device' => new DeviceResource($device),
            ],
            $code
        );
    }
}
