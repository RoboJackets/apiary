<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class InfoController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'info' => [
                'appName' => config('app.name'),
                'appEnv' => config('app.env'),
                'allocId' => config('app.alloc_id'),
                'release' => config('sentry.release'),
                'oAuthClients' => [
                    'android' => [
                        'clientId' => config('oauth.android.client_id'),
                    ],
                    'ios' => [
                        'clientId' => config('oauth.ios.client_id'),
                    ],
                ],
            ],
        ]);
    }
}
