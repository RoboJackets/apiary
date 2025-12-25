<?php

namespace App\Http\Controllers;

use App\Http\Requests\DynamicClientRegistrationRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\ClientRepository;

class OAuthController
{
    public function __construct(private ClientRepository $clientRepository)
    {
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

    public function registerClient(DynamicClientRegistrationRequest $request): JsonResponse
    {
        $client = $this->clientRepository->createClientCredentialsGrantClient(name: $request->client_name);

        return response()->json(
            data: [
                "client_id" => $client->id,
                "client_secret" => $client->plain_secret,
                "client_id_issued_at" => $client->created_at->timestamp,
                "client_secret_expires_at" => 0,
            ],
            status: 201
        );
    }
}
