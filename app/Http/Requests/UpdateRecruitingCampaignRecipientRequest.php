<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecruitingCampaignRecipientRequest extends FormRequest
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
                'recruiting_campaign_id' => 'exists:recruiting_campaigns,id|numeric|nullable',
                'email_address'          => 'nullable',
                'recruiting_visit_id'    => 'exists:recruiting_visits,id|nullable',
                'user_id'                => 'exists:users,id|numeric|nullable',
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
