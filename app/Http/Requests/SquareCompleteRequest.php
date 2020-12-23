<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SquareCompleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'checkoutId' => [
                'required',
                'string',
                'alpha_dash',
                'exists:payments,checkout_id',
            ],
            'referenceId' => [
                'required',
                'integer',
                'exists:payments,id',
            ],
        ];
    }
}
