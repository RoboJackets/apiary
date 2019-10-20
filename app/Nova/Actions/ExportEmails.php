<?php

declare(strict_types=1);

namespace App\Nova\Actions;

use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportEmails extends DownloadExcel
{
    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Export Names and Emails';

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    public function __construct()
    {
        $this->withFilename('MemberEmails.csv')
            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
            ->only('preferred_first_name', 'last_name', 'gt_email')
            ->withHeadings('First Name', 'Last Name', 'GT Email')
            ->withChunkCount(1000);
    }

    /**
     * Indicates if this action is only available on the resource index view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;
}
