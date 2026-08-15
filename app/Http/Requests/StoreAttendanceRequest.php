<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @psalm-pure
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<string>>
     *
     * @psalm-pure
     */
    public function rules(): array
    {
        return [
            'attendable_type' => [
                'required',
                'string',
                'in:event,team',
            ],
            'attendable_id' => [
                'required',
                'numeric',
            ],
            'gtid' => [
                'required',
                'numeric',
                'digits:9',
            ],
            'source' => [
                'required',
                'string',
            ],
            'reader' => [
                'sometimes',
                'array',
            ],
            'reader.serial_number' => [
                'required_with:reader',
                'digits:7',
            ],
            'reader.manufacturer' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.model' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.hardware_version' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.bluetooth_firmware_version' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.bluetooth_software_version' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.bootloader_version' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.application_version' => [
                'required_with:reader',
                'string',
                'max:255',
            ],
            'reader.battery_percentage' => [
                'required_with:reader',
                'integer',
                'between:1,100',
            ],
        ];
    }
}
