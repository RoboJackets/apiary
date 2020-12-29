<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\SquareCashTransaction;
use Illuminate\Console\Command;

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
            $possibleTransactions = SquareCashTransaction::leftJoin(
                'payments',
                'square_cash_transactions.transaction_id',
                '=',
                'payments.square_cash_transaction_id'
            )
                ->whereNull('payments.id')
                ->where('square_cash_transactions.amount', '>=', 50)
                ->where('square_cash_transactions.amount', '<', 200)
                ->get();

            $bar = $this->output->createProgressBar(count($possibleTransactions));
            $bar->start();

            foreach ($possibleTransactions as $squareCashTransaction) {
                $this->newLine();
                $this->table(
                    [
                        'transaction_id',
                        'transaction_timestamp',
                        'amount',
                        'note',
                        'name_of_sender',
                    ],
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

                if (1 === $paymentCount) {
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
        } else {
            $possibleTransactions = DuesTransaction::crossJoin(
                'payments',
                'dues_transactions.id',
                '=',
                'payable_id'
            )
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
        }
    }
}
