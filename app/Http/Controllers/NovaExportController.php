<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
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
        try {
            $path = Storage::path('nova-exports/'.$file);
            return response()->download($path)->deleteFileAfterSend(true);
        } catch (FileNotFoundException $exception) {
            Log::error("FileNotFoundException while retrieving file $file", [$exception->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        } catch (\Exception $exception) {
            Log::error("Generic Exception while retrieving file $file", [$exception->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }

    }
}
