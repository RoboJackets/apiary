<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ResumeBookController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-attendance', ['only' => ['show']]);
    }

    /**
     * Show a resume book.
     *
     * @param string $tag
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(string $hash)
    {
        if (! ctype_alnum($hash)) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'invalid_datecode',
                ],
                400
            );
        }

        $file = 'attendance-reports/'.$hash.'.csv';

        $response = response()->file(Storage::disk('local')->path($file));

        Storage::delete($file);

        return $response;
    }
}
