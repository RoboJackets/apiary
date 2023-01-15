<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'preferred_first_name' => [
                'max:127',
            ],
            'phone' => [
                'digits_between:10,15',
                'nullable',
            ],
            'emergency_contact_name' => [
                'required_with:emergency_contact_phone',
                'regex:/^[a-zA-Z\s]+$/',
                'max:255',
                'nullable',
            ],
            'emergency_contact_phone' => [
                'required_with:emergency_contact_name',
                'digits_between:10,15',
                'different:phone',
                'nullable',
            ],
            'graduation_semester' => [
                'max:6',
            ],
            'shirt_size' => [
                'in:'.implode(',', array_keys(User::$shirt_sizes)),
                'nullable',
            ],
            'polo_size' => [
                'in:'.implode(',', array_keys(User::$shirt_sizes)),
                'nullable',
            ],
            'generateToken' => [
                'boolean',
            ],
            'gender' => [
                'string',
                'nullable',
            ],
            'ethnicity' => [
                'string',
                'nullable',
            ],
            'github_invite_pending' => [
                'boolean',
            ],
            'exists_in_sums' => [
                'boolean',
            ],
            'clickup_email' => [
                'string',
                'nullable',
                'email:rfc,strict,dns,spoof',
            ],
            'clickup_id' => [
                'integer',
                'nullable',
            ],
            'clickup_invite_pending' => [
                'boolean',
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
