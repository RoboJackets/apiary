<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MembershipAgreementCompleteRequest extends FormRequest
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
            'ticket' => [
                'required',
                'string',
            ],
            'hash' => [
                'required',
                'string',
                'alpha_num',
                'exists:signatures,cas_service_url_hash',
            ],
        ];
    }
}
