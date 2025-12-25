<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class DynamicClientRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Request $request): bool
    {
        return config('features.sandbox-mode', false) ||
            IpUtils::checkIp($request->ip(), config('oauth.dynamic_client_registration_cidrs')) ||
            IpUtils::checkIp($request->ip(), '127.0.0.0/8');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'redirect_uris' => [
                'prohibited',
            ],
            'grant_types' => [
                'required',
                'array',
                'size:1',
            ],
            'grant_types.*' => [
                'required',
                'string',
                'in:client_credentials',
            ],
            'client_name' => [
                'required',
                'string',
                'min:2',
                'max:64',
                'unique:oauth_clients,name',
            ],
        ];
    }
}
