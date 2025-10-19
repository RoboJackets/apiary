<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'uid' => [
                'required',
                'unique:users',
                'max:127',
            ],
            'gtid' => [
                'required',
                'unique:users',
                'max:10',
            ],
            'gt_email' => [
                'required',
                'unique:users',
                'max:255',
                'email:rfc,strict,dns,spoof',
            ],
            'first_name' => [
                'required',
                'max:127',
            ],
            'last_name' => [
                'required',
                'max:127',
            ],
            'preferred_first_name' => [
                'max:127',
            ],
            'phone' => [
                'max:15',
            ],
            'emergency_contact_name' => [
                'max:255',
            ],
            'emergency_contact_phone' => [
                'max:15',
            ],
            'join_semester' => [
                'max:6',
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
        ];
    }
}
