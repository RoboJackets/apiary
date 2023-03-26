<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class NovaExportController extends Controller
{
    /**
     * Trigger a download of the specified file.
     */
    public function export(string $file): Response
    {
        $path = Storage::path('nova-exports/'.$file);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
