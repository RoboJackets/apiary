<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(string $tag)
    {
        if (strlen(preg_replace('/[-0-9a-zA-Z]/', '', $tag)) > 0) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'invalid_datecode',
                ],
                400
            );
        }

        return response()->file(Storage::disk('local')->path('resumes/robojackets-resume-book-'.$tag.'.pdf'));
    }
}
