<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class InfoController
{
    public function showInfo(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'info' => [
                'appName' => config('app.name'),
                'appEnv' => config('app.env'),
                'allocId' => config('app.alloc_id'),
                'release' => config('sentry.release'),
                'oAuthClients' => [
                    'reactNative' => [
                        'clientId' => config('oauth.android.client_id'),
                    ],
                ],
            ],
        ]);
    }

    public function showOpenIdConfiguration(): JsonResponse
    {
        return response()->json([
            'issuer' => route('home'),
            'authorization_endpoint' => route('passport.authorizations.authorize'),
            'jwks_uri' => route('openid.jwks'),
            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'token_endpoint' => route('passport.token'),
        ]);
    }
}
