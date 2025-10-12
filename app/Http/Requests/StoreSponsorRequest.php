<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'unique:sponsors',
            ],
            'end_date' => [
                'date_time',
            ],
            'domain_names' => [
                'array<string>',
            ],

        ];
    }
}
