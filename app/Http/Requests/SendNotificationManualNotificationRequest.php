<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationManualNotificationRequest extends FormRequest
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
            'emails' => [
                'required',
                'array',
            ],
            'emails.*' => [
                'required',
                'email:rfc,strict,dns,spoof',
            ],
            'template_type' => [
                'required',
                'in:recruiting,database',
            ],
            'template_id' => [
                'numeric',
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
