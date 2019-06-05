<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
                'name'                 => 'required|max:255',
                'cost'                 => 'numeric',
                'allow_anonymous_rsvp' => 'required|boolean',
                'organizer_id'         => 'required|exists:users,id',
                'location'             => 'max:255',
                'start_time'           => 'date',
                'end_time'             => 'date',
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
