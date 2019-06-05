<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
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
                'name'               => 'required|string|unique:teams',
                'description'        => 'string|max:4096|nullable',
                'attendable'         => 'boolean',
                'visible'            => 'boolean',
                'self_serviceable'   => 'boolean',
                'mailing_list_name'  => 'string|nullable',
                'slack_channel_id'   => 'string|nullable',
                'slack_channel_name' => 'string|nullable',
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
