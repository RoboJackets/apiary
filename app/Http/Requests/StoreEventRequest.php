<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
                'max:255',
            ],
            'allow_anonymous_rsvp' => [
                'required',
                'boolean',
            ],
            'organizer_id' => [
                'required',
                'exists:users,id',
            ],
            'location' => [
                'max:255',
            ],
            'start_time' => [
                'date',
            ],
            'end_time' => [
                'date',
            ],
        ];
    }
}
