<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<string>>
     */
    public function rules(): array
    {
        return [
            'attendable_type' => [
                'required',
                'string',
            ],
            'attendable_id' => [
                'required',
                'numeric',
            ],
            'gtid' => [
                'required_without:access_card_number',
                'numeric',
            ],
            'access_card_number' => [
                'string',
                'numeric',
            ],
            'source' => [
                'required',
                'string',
            ],
            'created_at' => [
                'date',
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [];
    }
}
