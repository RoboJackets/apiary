<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @psalm-pure
     */
    public function authorize(): bool
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
            'name' => [
                'required',
                'unique:roles',
            ],
        ];
    }
}
