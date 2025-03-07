<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchAttendanceRequest extends FormRequest
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
            'attendable_type' => [
                'required',
            ],
            'attendable_id' => [
                'required',
                'numeric',
            ],
            'start_date' => [
                'date',
                'nullable',
            ],
            'end_date' => [
                'date',
                'nullable',
            ],
        ];
    }
}
