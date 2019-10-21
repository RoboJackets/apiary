<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ResumeBookController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-users-resume', ['only' => ['show']]);
    }

    /**
     * Show a resume book.
     *
     * @param string $datecode
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(string $datecode)
    {
        if (strlen(preg_replace('/[-0-9]/', '', $datecode)) > 0) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'invalid_datecode',
                ],
                400
            );
        }

        return response()->file(Storage::disk('local')->path('resumes/resume-book-'.$datecode.'.pdf'));
    }
}
