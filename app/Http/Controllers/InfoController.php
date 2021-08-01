<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'info' => [
                'appName' => config('app.name'),
                'appEnv' => config('app.env'),
            ],
        );
    }
}
