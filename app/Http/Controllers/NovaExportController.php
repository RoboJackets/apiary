<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NovaExportController extends Controller
{
    /**
     * @param string $file
     *
     * @return JsonResponse|BinaryFileResponse
     */
    public function export(string $file)
    {
        $path = Storage::path('nova-exports/'.$file);
        return response()->download($path)->deleteFileAfterSend(true);
    }
}
