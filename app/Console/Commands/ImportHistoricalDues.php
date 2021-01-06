<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Imports\HistoricalDues;
use Illuminate\Console\Command;

class ImportHistoricalDues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:dues {year : the fiscal year in the file} {file : the dues file to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports historical dues information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting dues import from file');
        (new HistoricalDues($this, intval($this->argument('year'))))
            ->withOutput($this->output)
            ->import($this->argument('file'));
        $this->info('Import successful');
    }
}
