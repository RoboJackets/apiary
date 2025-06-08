<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class NovaExportController
{
    /**
     * Trigger a download of the specified file.
     */
    public function export(string $file)
    {
        $path = Storage::path('nova-exports/'.$file);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
