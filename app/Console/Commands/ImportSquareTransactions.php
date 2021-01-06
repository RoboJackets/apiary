<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Imports\SquareTransactions;
use Illuminate\Console\Command;

class ImportSquareTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:square {file : the file of Square transactions to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Square transactions from a provided file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting Square Cash transaction import from file');
        (new SquareTransactions())->withOutput($this->output)->import($this->argument('file'));
        $this->info('Import successful');
    }
}
