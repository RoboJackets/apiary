<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportGtid extends DownloadExcel
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export GTIDs';

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    public function __construct() {
        $this->withFilename('Members.csv')
            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ->only('gtid');
    }
}
