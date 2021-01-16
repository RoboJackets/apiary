<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MembershipAgreementRedirectRequest extends FormRequest
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
            'over18' => [
                'required',
                'accepted',
            ],
            'eSignConsent' => [
                'required',
                'accepted',
            ],
            'acknowledgement' => [
                'required',
                'accepted',
            ],
        ];
    }
}
