<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceRequest;
use App\Http\Resources\Device as DeviceResource;
use App\Util\DeviceInventory;
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
        $device = DeviceInventory::upsert($request, $request->validated());

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
