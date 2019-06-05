<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'slack_id' => [
               'max:21',
               'nullable',
               Rule::unique('users')->ignore($user->id),
              ], 'personal_email' => [
                                      'max:255',
                                      'nullable',
                                      Rule::unique('users')->ignore($user->id),
                                     ], 'first_name' => 'max:127', 'last_name' => 'max:127', 'middle_name' => 'max:127', 'preferred_name' => 'max:127', 'phone' => 'max:15', 'emergency_contact_name' => 'max:255', 'emergency_contact_phone' => 'max:15', 'join_semester' => 'max:6', 'graduation_semester' => 'max:6', 'shirt_size' => 'in:s,m,l,xl,xxl,xxxl|nullable', 'polo_size' => 'in:s,m,l,xl,xxl,xxxl|nullable', 'accept_safety_agreement => date|nullable', 'generateToken' => 'boolean', 'gender' => 'string|nullable', 'ethnicity' => 'string|nullable',
               ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }
}
