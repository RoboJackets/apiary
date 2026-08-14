<?php

declare(strict_types=1);

namespace App\Util;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class DeviceInventory
{
    /**
     * Upsert a Device from an inventory report.
     *
     * Validates the requester is a User and the request has an IP address,
     * then updates or creates the device with the provided data and request metadata.
     *
     * @param  array<string, string|int>  $data
     *
     * @throws HttpResponseException
     */
    public static function upsert(Request $request, array $data): Device
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'A user token is required.',
            ], 401));
        }

        $ipAddress = $request->ip();

        if ($ipAddress === null) {
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'last_seen_ip_address is required.',
            ], 422));
        }

        return Device::updateOrCreate(
            ['serial_number' => $data['serial_number']],
            array_merge(
                $data,
                [
                    'last_seen_user_id' => $user->id,
                    'last_seen_at' => now(),
                    'last_seen_ip_address' => $ipAddress,
                ]
            )
        );
    }
}
