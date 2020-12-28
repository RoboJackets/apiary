<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\SquareCashTransaction;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class SquareCashTransactions implements ToModel, WithHeadingRow, WithProgressBar
{
    use Importable;

    /**
     * Convert a Square Cash transaction row into a model if it is an inbound payment
     *
     * @param array<string,?string> $row
     *
     * @phan-suppress PhanTypeMismatchArgumentNullableInternal
     */
    public function model(array $row): ?SquareCashTransaction
    {
        if ('PAYMENT DEPOSITED' !== $row['status']) {
            return null;
        }

        return new SquareCashTransaction([
            'transaction_id' => $row['transaction_id'],
            'transaction_timestamp' => $row['date'],
            'amount' => substr($row['amount'], 1),
            'note' => $row['notes'],
            'name_of_sender' => $row['name_of_senderreceiver'],
        ]);
    }
}
