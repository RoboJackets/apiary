<?php

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
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

    public function __construct()
    {
        $this->withFilename('Members.csv')
            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ->only('gtid');
    }
}
