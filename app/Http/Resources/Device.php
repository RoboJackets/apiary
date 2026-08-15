<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Device extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'hardware_version' => $this->hardware_version,
            'bluetooth_firmware_version' => $this->bluetooth_firmware_version,
            'bluetooth_software_version' => $this->bluetooth_software_version,
            'bootloader_version' => $this->bootloader_version,
            'application_version' => $this->application_version,
            'battery_percentage' => $this->battery_percentage,
            'last_seen_user_id' => $this->last_seen_user_id,
            'last_seen_at' => $this->last_seen_at,
            'last_seen_ip_address' => $this->last_seen_ip_address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
