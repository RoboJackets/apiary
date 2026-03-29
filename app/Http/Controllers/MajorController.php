<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\Major as MajorResource;
use App\Models\Major;
use Illuminate\Http\JsonResponse;

class MajorController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'majors' => MajorResource::collection(Major::all()),
        ]);
    }
}
