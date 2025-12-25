<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class OAuthController
{
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
