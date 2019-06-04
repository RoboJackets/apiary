<?php

declare(strict_types=1);

namespace App\Nova\Actions;

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
            ->only('first_name', 'last_name', 'gtid')
            ->withChunkCount(1000);
    }

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;
}
