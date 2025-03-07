<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
     * @return array<string,array<string|\Illuminate\Validation\Rules\In>>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
            ],
            'method' => [
                'required',
                'string',
                Rule::in(array_keys(Payment::$methods)),
            ],
            'recorded_by' => [
                'required',
                'numeric',
                'exists:users,id',
            ],
            'payable_type' => [
                'required',
                'string',
            ],
            'payable_id' => [
                'required',
                'numeric',
            ],
        ];
    }
}
