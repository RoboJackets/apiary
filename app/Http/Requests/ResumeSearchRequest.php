<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResumeSearchRequest extends FormRequest
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
     * @psalm-pure
     *
     * @return array<string,string|list<string>>
     */
    public function rules(): array
    {
        return [
            'majors' => 'nullable|array',
            'majors.*' => 'sometimes|string|exists:majors,display_name',
            'graduation_semesters' => 'nullable|array',
            'graduation_semesters.*' => 'sometimes|integer|digits:6',
        ];
    }
}
