<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Imports\SquareCashTransactions;
use Illuminate\Console\Command;

class ImportSquareCashTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:squarecash {file : the file of Square Cash transactions to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Square Cash transactions from a provided file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting Square Cash transaction import from file');
        (new SquareCashTransactions())->withOutput($this->output)->import($this->argument('file'));
        $this->info('Import successful');
    }
}
