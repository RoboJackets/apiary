<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportContactInfo extends DownloadExcel
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export Contact Info';

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    public function __construct()
    {
        $this->withFilename('MemberContactInfo.csv')
            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ->only('name', 'gt_email', 'personal_email', 'phone')
            ->withHeadings()
            ->withChunkCount(1000);
    }

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;
}
