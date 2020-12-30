<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\SquareCashTransaction;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;

class MatchSquareCashTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:squarecash {--interactive : Whether to use interactive mode }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to match SquareCashTransactions to DuesTransactions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (true === $this->option('interactive')) {
            $this->info('Manually matching Square Cash transactions');
            $possibleTransactions = SquareCashTransaction::select(
                'square_cash_transactions.transaction_id',
                'square_cash_transactions.transaction_timestamp',
                'square_cash_transactions.amount',
                'square_cash_transactions.note',
                'square_cash_transactions.name_of_sender'
            )->leftJoin(
                'payments',
                'square_cash_transactions.transaction_id',
                '=',
                'payments.square_cash_transaction_id'
            )->whereNull('payments.id')
            ->where('square_cash_transactions.amount', '>=', 50)
            ->where('square_cash_transactions.amount', '<', 200)
            ->orderBy('square_cash_transactions.transaction_timestamp')
            ->get();

            $bar = $this->output->createProgressBar(count($possibleTransactions));
            $bar->start();

            foreach ($possibleTransactions as $squareCashTransaction) {
                $this->newLine();
                $this->table(
                    array_keys($squareCashTransaction->toArray()),
                    [
                        $squareCashTransaction->toArray(),
                    ]
                );

                $this->newLine();
                $id = $this->ask('Enter DuesTransaction ID or leave blank to skip');

                if (null === $id) {
                    $bar->advance();
                    continue;
                }

                $duesTransaction = DuesTransaction::where('id', $id)->firstOrFail();

                $paymentCount = Payment::where('payable_id', $duesTransaction->id)->count();

                if (0 === $paymentCount) {
                    $payment = new Payment();
                    $payment->payable_id = $duesTransaction->id;
                    $payment->payable_type = $duesTransaction->getMorphClass();
                    $payment->notes = 'Historical dues import';
                } elseif (1 === $paymentCount) {
                    $payment = Payment::where('payable_id', $duesTransaction->id)->firstOrFail();
                } else {
                    $this->newLine();
                    $id = $this->ask(
                        'Found '.$paymentCount.' associated payments - enter Payment ID or leave blank to skip'
                    );

                    if (null === $id) {
                        $bar->advance();
                        continue;
                    }

                    $payment = Payment::where('id', $id)->firstOrFail();
                }

                $payment->updateFromSquareCashTransaction($squareCashTransaction);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Manual match successful');
        } else {
            $this->info('Automatching Square Cash transactions');
            $possibleTransactions = DuesTransaction::select(
                'dues_transactions.id',
                'dues_transactions.user_id',
                'dues_transactions.dues_package_id'
            )
            ->leftJoin('payments', static function (JoinClause $join): void {
                $join->on('dues_transactions.id', '=', 'payable_id')
                     ->where('payments.amount', '>', 0);
            })
            ->whereNull('payments.square_cash_transaction_id')
            ->get();

            $bar = $this->output->createProgressBar(count($possibleTransactions));
            $bar->start();

            foreach ($possibleTransactions as $duesTransaction) {
                $paymentCount = Payment::where('payable_id', $duesTransaction->id)->count();

                if (1 !== $paymentCount) {
                    $bar->advance();
                    continue;
                }

                $payment = Payment::where('payable_id', $duesTransaction->id)->firstOrFail();

                $squareCashTransaction = SquareCashTransaction::guessFromDuesTransaction($duesTransaction);

                if (null !== $squareCashTransaction) {
                    $payment->updateFromSquareCashTransaction($squareCashTransaction);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Automatch successful');
        }
    }
}
