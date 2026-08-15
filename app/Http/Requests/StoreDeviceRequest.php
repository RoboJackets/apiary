<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
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
     * @return array<string, array<string>>
     *
     * @psalm-pure
     */
    public function rules(): array
    {
        return [
            'serial_number' => [
                'required',
                'digits:7',
            ],
            'manufacturer' => [
                'required',
                'string',
                'max:255',
            ],
            'model' => [
                'required',
                'string',
                'max:255',
            ],
            'hardware_version' => [
                'required',
                'string',
                'max:255',
            ],
            'bluetooth_firmware_version' => [
                'required',
                'string',
                'max:255',
            ],
            'bluetooth_software_version' => [
                'required',
                'string',
                'max:255',
            ],
            'bootloader_version' => [
                'required',
                'string',
                'max:255',
            ],
            'application_version' => [
                'required',
                'string',
                'max:255',
            ],
            'battery_percentage' => [
                'required',
                'integer',
                'between:1,100',
            ],
        ];
    }
}
