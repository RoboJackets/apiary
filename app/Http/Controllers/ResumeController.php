<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreResumeRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ResumeController implements HasMiddleware
{
    #[\Override]
    public static function middleware(): array
    {
        return [
            new Middleware('permission:read-users-resume|read-users-own', only: ['show']),
            new Middleware('permission:update-users-resume|update-users-own', only: ['store']),
        ];
    }

    /**
     * Show the user's resume.
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(string $id, Request $request)
    {
        $user = User::findByIdentifier($id)->first();
        if ($user !== null) {
            if (! $request->user()->can('read-users-resume') && $request->user()->id !== $user->id) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'You do not have permission to view that resume.',
                    ],
                    403
                );
            }

            try {
                return response()->file(Storage::disk('local')->path('resumes/'.$user->uid.'.pdf'));
            } catch (FileNotFoundException) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'The requested user has no resume.',
                    ],
                    404
                );
            }
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
     * Get the MIME type of a file using the system `file` command.
     */
    private function getFileCommandMimeType(string $filePath): ?string
    {
        $output = null;
        // --mime-type to get just the MIME type
        // -b to be "brief" and return *just* the MIME type
        exec('file --mime-type -b '.escapeshellarg($filePath), $output);

        if (count($output) === 0) {
            return null;
        }

        $output = $output[0];

        // Sanity check to make sure we got a MIME type back, rather than an error (file names can't contain the /
        // character so that was a good indicator)
        if ($output !== null && strpos($output, '/') !== false && strpos($output, 'cannot open') === false) {
            return $output;
        }

        return null;
    }

    /**
     * Store the user's resume.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(string $id, StoreResumeRequest $request)
    {
        $user = User::findByIdentifier($id)->first();
        if ($user !== null) {
            if (! $request->user()->can('update-users-resume') && $request->user()->id !== $user->id) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'You do not have permission to upload that resume.',
                    ],
                    403
                );
            }

            if ($user->is_active !== true) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'inactive',
                    ],
                    403
                );
            }

            if ($user->is_student !== true) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'ineligible',
                    ],
                    403
                );
            }

            // Make sure there's exactly one file
            $file = $request->file('resume');
            if ($file === null || is_array($file)) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'resume_required',
                    ],
                    400
                );
            }

            // 1MB file size limit
            if ($file->getSize() > 1000000) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'too_big',
                    ],
                    400
                );
            }

            $tempPath = $file->getPathname();
            $PDF_MIME_TYPE = 'application/pdf';

            $fileCommandMimeType = $this->getFileCommandMimeType($tempPath);

            if ($PDF_MIME_TYPE !== $fileCommandMimeType) {
                Log::debug(
                    'User resume uploaded for user '.$user->uid.' but was invalid (`file` command\'s '.
                    'reported MIME type was '.$fileCommandMimeType.')'
                );

                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'resume_not_pdf',
                    ],
                    400
                );
            }

            $exifReturn = -1;
            $exifOutput = '';
            exec('exiftool -json '.escapeshellarg($tempPath), $exifOutput, $exifReturn);

            // @phan-suppress-next-line PhanTypeArraySuspiciousNullable
            $exifOutput = json_decode(implode(' ', $exifOutput), true)[0];
            $fileType = array_key_exists('FileType', $exifOutput) ? $exifOutput['FileType'] : null;
            $mimeType = array_key_exists('MIMEType', $exifOutput) ? $exifOutput['MIMEType'] : null;
            $pageCount = array_key_exists('PageCount', $exifOutput) ? $exifOutput['PageCount'] : -1;
            $exifError = array_key_exists('Error', $exifOutput) ? $exifOutput['Error'] : null;

            $valid = $exifError === null && $fileType === 'PDF' && $PDF_MIME_TYPE === $mimeType;
            $pageCountValid = $pageCount === 1;
            $exifErrorInvalidType = $exifError === 'Unknown file type';

            if (! $valid || ! $pageCountValid) {
                Log::debug('User resume uploaded for user '.$user->uid.', but was invalid (PDF: '
                    .($valid ? 'true' : 'false').', one page: '.($pageCountValid ? 'true' : 'false').', Error: '
                    .$exifError.')');
                $error = $valid ? 'resume_not_one_page' : 'resume_not_pdf';
                if ($exifError && ! $exifErrorInvalidType) {
                    Log::error('exiftool responded with unknown error');
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

    public function showUploadPage(Request $request)
    {
        return view('users/resumeupload', ['id' => $request->user()->id]);
    }
}
