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
            'hardware_version' => [
                'required',
                'string',
                'max:255',
            ],
            'software_version' => [
                'required',
                'string',
                'max:255',
            ],
            'firmware_version' => [
                'required',
                'string',
                'max:255',
            ],
            'battery_percentage' => [
                'required',
                'integer',
                'between:0,100',
            ],
        ];
    }
}
