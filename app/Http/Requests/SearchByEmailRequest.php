<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchByEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @psalm-pure
     */
    public function authorize(): true
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,array<string>>
     *
     * @psalm-pure
     */
    public function rules(): array
    {
        return [
            'email' => [
                'string',
                'required',
                'email:rfc,strict,dns,spoof,filter',
            ],
        ];
    }
}
