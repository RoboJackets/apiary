<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDuesPackageRequest extends FormRequest
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
                'string',
            ],
            'effective_start' => [
                'date',
            ],
            'effective_end' => [
                'date',
            ],
            'cost' => [
                'numeric',
            ],
        ];
    }
}
