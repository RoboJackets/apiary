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
                'regex:/^([a-zA-Z]+\s)*[a-zA-Z]+$/',
            ],
            'phone' => [
                'numeric',
                'nullable',
            ],
            'phone_verified' => [
                'nullable',
                'boolean',
            ],
            'emergency_contact_name' => [
                'required_with:emergency_contact_phone',
                'regex:/^[a-zA-Z\s]+$/',
                'max:255',
                'nullable',
            ],
            'emergency_contact_phone' => [
                'required_with:emergency_contact_name',
                'numeric',
                'different:phone',
                'nullable',
            ],
            'emergency_contact_phone_verified' => [
                'nullable',
                'boolean',
            ],
            'graduation_semester' => [
                'regex:/^[0-9]{4}0[258]$/',
                'nullable',
            ],
            'shirt_size' => [
                'in:'.implode(',', array_keys(User::$shirt_sizes)),
                'nullable',
            ],
            'polo_size' => [
                'in:'.implode(',', array_keys(User::$shirt_sizes)),
                'nullable',
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
            'legal_gender' => [
                'in:M,F,X,U',
                'nullable',
            ],
            'date_of_birth' => [
                'date_format:Y-m-d',
                'nullable',
            ],
            'delta_skymiles_number' => [
                'integer',
                'digits:10',
                'nullable',
            ],
            'legal_middle_name' => [
                'string',
                'nullable',
            ],
        ];
    }
}
