<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDuesPackageRequest extends FormRequest
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
            ],
            'effective_start' => [
                'required',
                'date',
            ],
            'effective_end' => [
                'required',
                'date',
            ],
            'cost' => [
                'required',
                'numeric',
            ],
        ];
    }
}
