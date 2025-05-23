<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
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
            'name' => [
                'string',
            ],
            'description' => [
                'string',
                'max:4096',
                'nullable',
            ],
            'project_manager_id' => [
                'integer',
                'nullable',
                'exists:users,id',
            ],
            'attendable' => [
                'boolean',
            ],
            'visible' => [
                'boolean',
            ],
            'visible_on_kiosk' => [
                'boolean',
            ],
            'self_serviceable' => [
                'boolean',
            ],
            'mailing_list_name' => [
                'string',
                'nullable',
            ],
            'slack_channel_id' => [
                'string',
                'nullable',
            ],
            'slack_channel_name' => [
                'string',
                'nullable',
            ],
        ];
    }
}
