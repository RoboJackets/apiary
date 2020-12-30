<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\SquareTransaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class SquareTransactions implements ToModel, WithHeadingRow, WithProgressBar
{
    use Importable;

    /**
     * Convert a Square transaction row into a model if it is an inbound payment.
     *
     * @param array<string,?string> $row
     *
     * @phan-suppress PhanTypeMismatchArgumentNullableInternal
     */
    public function model(array $row): ?SquareTransaction
    {
        if ('Payment' !== $row['event_type']) {
            return null;
        }

        if (null !== $row['other_tender_type']) {
            return null;
        }

        if ('$0.00' !== $row['cash']) {
            return null;
        }

        if ('$0.00' === $row['total_collected']) {
            return null;
        }

        if (SquareTransaction::where('transaction_id', $row['transaction_id'])->count() > 0) {
            return null;
        }

        if (SquareTransaction::where('payment_id', $row['payment_id'])->count() > 0) {
            return null;
        }

        return new SquareTransaction([
            'transaction_timestamp' => Carbon::parse($row['date'].' '.$row['time'], config('app.timezone')),
            'amount' => str_replace(',', '', substr($row['total_collected'], 1)),
            'source' => $row['source'],
            'entry_method' => $row['card_entry_methods'],
            'processing_fee' => -floatval(substr($row['fees'], 1)),
            'transaction_id' => $row['transaction_id'],
            'payment_id' => $row['payment_id'],
            'card_brand' => $row['card_brand'],
            'last_4' => $row['pan_suffix'],
            'device_name' => $row['device_name'],
            'staff_name' => $row['staff_name'],
            'description' => substr($row['description'], 0, 255),
            'customer_name' => $row['customer_name'],
        ]);
    }
}
