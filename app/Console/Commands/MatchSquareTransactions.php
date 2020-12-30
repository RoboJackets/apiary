<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DuesTransaction;
use App\Models\Payment;
use App\Models\SquareTransaction;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;

class MatchSquareTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'match:square {--interactive : Whether to use interactive mode }';

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
            $this->info('Manually matching Square transactions');
            $possibleTransactions = SquareTransaction::select(
                'square_transactions.transaction_id',
                'square_transactions.transaction_timestamp',
                'square_transactions.amount',
                'square_transactions.source',
                'square_transactions.entry_method',
                'square_transactions.device_name',
                'square_transactions.staff_name',
                'square_transactions.description',
                'square_transactions.customer_name',
                'square_transactions.processing_fee',
                'square_transactions.card_brand',
                'square_transactions.last_4'
            )->leftJoin(
                'payments',
                'square_transactions.transaction_id',
                '=',
                'payments.server_txn_id'
            )->whereNull('payments.id')
            ->whereNotNull('square_transactions.customer_name')
            ->where('square_transactions.amount', '>=', 50)
            ->where('square_transactions.amount', '<', 200)
            ->orderBy('square_transactions.transaction_timestamp')
            ->get();

            $bar = $this->output->createProgressBar(count($possibleTransactions));
            $bar->start();

            foreach ($possibleTransactions as $squareTransaction) {
                $this->newLine();
                $this->table(
                    array_keys($squareTransaction->toArray()),
                    [
                        $squareTransaction->toArray(),
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

                $payment->updateFromSquareTransaction($squareTransaction);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Manual match successful');
        } else {
            $this->info('Automatching Square transactions');
            $possibleTransactions = DuesTransaction::select(
                'dues_transactions.id',
                'dues_transactions.user_id',
                'dues_transactions.dues_package_id'
            )
            ->leftJoin('payments', static function (JoinClause $join): void {
                $join->on('dues_transactions.id', '=', 'payable_id')
                     ->where('payments.amount', '>', 0);
            })
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
            $this->newLine();
            $this->info('Automatch successful');
        }
    }
}
