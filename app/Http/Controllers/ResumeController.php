<?php

declare(strict_types=1);

// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter,SlevomatCodingStandard.Functions.UnusedParameter

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreResumeRequest;

class ResumeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:read-users-resume|read-users-own', ['only' => ['showResume']]);
        $this->middleware('permission:update-users-resume|update-users-own', ['only' => ['storeResume']]);
        $this->middleware('permission:delete-users-resume|update-users-own', ['only' => ['deleteResume']]);
        $this->middleware('permission:read-users-resume', ['only' => ['showResumeBook']]);
    }

    /**
     * Show the user's resume.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function showResume(string $id)
    {
        $user = User::findByIdentifier($id)->first();
        if ($user) {
            return response()->file(Storage::disk('local')->path('resumes/'.$user->uid.'.pdf'));
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'User does not exist or was previously deleted.',
            ],
            422
        );
    }

    /**
     * Show a resume book.
     *
     * @param string $datecode
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function showResumeBook(string $datecode)
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

    /**
     * Store the user's resume.
     *
     * @param string $id
     * @param StoreResumeRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function storeResume(string $id, StoreResumeRequest $request)
    {
        $user = User::findByIdentifier($id)->first();
        if ($user) {
            if (! $user->is_active) {
                if ($request->has('redirect')) {
                    return redirect()->route('resume.index', ['resume_error' => 'inactive']);
                }

                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'inactive',
                    ],
                    400
                );
            }

            $file = $request->file('resume');
            if (null === $file || is_array($file)) {
                if ($request->has('redirect')) {
                    return redirect()->route('resume.index', ['resume_error' => 'resume_required']);
                }

                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'resume_required',
                    ],
                    400
                );
            }

            $tempPath = $file->getPathname();
            $exifReturn = -1;
            $exifOutput = '';
            exec('exiftool -json '.escapeshellarg($tempPath), $exifOutput, $exifReturn);

            $exifOutput = json_decode(implode(' ', $exifOutput), true)[0];
            $fileType = array_key_exists('FileType', $exifOutput) ? $exifOutput['FileType'] : null;
            $mimeType = array_key_exists('MIMEType', $exifOutput) ? $exifOutput['MIMEType'] : null;
            $pageCount = array_key_exists('PageCount', $exifOutput) ? $exifOutput['PageCount'] : -1;
            $exifError = array_key_exists('Error', $exifOutput) ? $exifOutput['Error'] : null;

            $valid = null === $exifError && 'PDF' === $fileType && 'application/pdf' === $mimeType;
            $pageCountValid = 1 === $pageCount;
            $exifErrorInvalidType = 'Unknown file type' === $exifError;

            if (! $valid || ! $pageCountValid) {
                \Log::debug('User resume uploaded for user '.$user->uid.', but was invalid (PDF: '
                    .($valid ? 'true' : 'false').', one page: '.($pageCountValid ? 'true' : 'false').', Error: '
                    .($exifError ? 'true' : 'false').')');
                $error = $valid ? 'resume_not_one_page' : 'resume_not_pdf';
                if ($exifError && ! $exifErrorInvalidType) {
                    \Log::error('exiftool responded with unknown error');
                    $error = 'unknown_error';
                }

                if ($request->has('redirect')) {
                    return redirect()->route('resume.index', ['resume_error' => $error]);
                }

                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $error,
                    ],
                    400
                );
            }

            // Store in the resumes folder with the user's username
            $file->storeAs('resumes', $user->uid.'.pdf');

            $user->resume_date = now();
            $user->save();

            if ($request->has('redirect')) {
                return redirect()->route('resume.index');
            }

            return response()->json(
                [
                    'status' => 'success',
                ],
                200
            );
        }

        return response()->json(
            [
                'status' => 'error',
                'message' => 'User does not exist or was previously deleted.',
            ],
            422
        );
    }

    /**
     * Delete the user's resume.
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteResume(string $id): JsonResponse
    {
        return response()->json(
            [
                'status' => 'error',
                'message' => 'unimplemented',
            ],
            501
        );
    }
}
