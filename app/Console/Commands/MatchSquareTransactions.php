<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\SquareTransaction;
use Illuminate\Console\Command;

class MatchSquareTransactions extends Command
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
    protected $description = 'Attempts to match SquareTransactions to DuesTransactions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (true === $this->option('interactive')) {
            $possibleTransactions = SquareTransaction::leftJoin(
                'payments',
                'square_transactions.transaction_id',
                '=',
                'payments.server_txn_id'
            )
                ->whereNull('payments.id')
                ->whereNotNull('square_transactions.customer_name')
                ->where('square_transactions.amount', '>=', 50)
                ->where('square_transactions.amount', '<', 200)
                ->get();

            $bar = $this->output->createProgressBar(count($possibleTransactions));
            $bar->start();

            foreach ($possibleTransactions as $squareTransaction) {
                $this->newLine();
                $this->table(
                    [
                        'transaction_id',
                        'transaction_timestamp',
                        'amount',
                        'source',
                        'description',
                        'entry_method',
                        'customer_name',
                    ],
                    [
                        $squareTransaction->toArray(),
                    ]
                );

                $this->newLine();
                $id = $this->ask('Enter DuesTransaction ID or leave blank to skip.');

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
                        'Found '.$paymentCount.' associated payments. Enter Payment ID or leave blank to skip.'
                    );

                    if (null === $id) {
                        $bar->advance();
                        continue;
                    }

                    $payment = Payment::where('id', $id)->firstOrFail();
                }

                $payment->updateFromSquareTransaction($squareTransaction);

                $bar->advance();
            }

            $bar->finish();
        } else {
            $possibleTransactions = DuesTransaction::crossJoin('payments', 'dues_transaction.id', '=', 'payable_id')
                ->whereNull('payments.server_txn_id')
                ->orWhereNull('payments.processing_fee')
                ->orWhereNull('payments.card_brand')
                ->orWhereNull('payments.last_4')
                ->orWhereNull('payments.entry_method')
                ->get();

            $bar = $this->output->createProgressBar(count($possibleTransactions));
            $bar->start();

            foreach ($possibleTransactions as $duesTransaction) {
                $paymentCount = Payment::where('payable_id', $duesTransaction->id)->count();

                if (1 !== $paymentCount) {
                    continue;
                }

                $payment = Payment::where('payable_id', $duesTransaction->id)->firstOrFail();

                $squareTransaction = SquareTransaction::guessFromDuesTransaction($duesTransaction);

                if (null !== $squareTransaction) {
                    $payment->updateFromSquareTransaction($squareTransaction);
                }

                $bar->advance();
            }

            $bar->finish();
        }
    }
}
