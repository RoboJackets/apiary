<?php declare(strict_types = 1);

namespace App\Nova\Actions;

use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportUsername extends DownloadExcel
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export Usernames';

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    public function __construct()
    {
        $this->withFilename('Members.csv')->withWriterType(\Maatwebsite\Excel\Excel::CSV)->only('uid');
    }

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;
}
