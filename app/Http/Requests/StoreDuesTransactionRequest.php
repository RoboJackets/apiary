<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDuesTransactionRequest extends FormRequest
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
                'swag_shirt_provided' => 'boolean|nullable',
                'swag_polo_provided'  => 'boolean|nullable',
                'dues_package_id'     => 'required|exists:dues_packages,id',
                'payment_id'          => 'exists:payments,id',
                'user_id'             => 'exists:users,id',
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
