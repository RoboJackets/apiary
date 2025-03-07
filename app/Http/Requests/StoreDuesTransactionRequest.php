<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDuesTransactionRequest extends FormRequest
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
            'dues_package_id' => [
                'required',
                'exists:dues_packages,id',
            ],
            'payment_id' => [
                'exists:payments,id',
            ],
            'user_id' => [
                'exists:users,id',
            ],
            'merchandise.*' => [
                'exists:merchandise,id',
            ],
            'merchandise' => [
                'array',
            ],
        ];
    }
}
