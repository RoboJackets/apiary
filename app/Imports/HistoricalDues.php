<?php

declare(strict_types=1);

namespace App\Imports;

use App\Console\Commands\ImportHistoricalDues;
use App\Jobs\CreateOrUpdateUserFromBuzzAPI;
use App\Models\DuesPackage;
use App\Models\DuesTransaction;
use App\Models\FiscalYear;
use App\Models\Payment;
use App\Models\SquareCashTransaction;
use App\Models\SquareTransaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Row;
use OITNetworkServices\BuzzAPI;
use OITNetworkServices\BuzzAPI\Resources;
use Throwable;

class HistoricalDues implements WithHeadingRow, WithProgressBar, OnEachRow
{
    use Importable;

    private const USER_CREATION_REASON_STRING = 'historical_dues_import';

    /**
     * The calling command, used for interactively resolving ambiguous records.
     *
     * @var \App\Console\Commands\ImportHistoricalDues
     */
    private $command;

    /**
     * The fiscal year being imported.
     *
     * @var int
     */
    private $fiscalYear;

    private const VALID_SHIRT_SIZES = [  // couldn't figure out how to add xs to enum
        's',
        'm',
        'l',
        'xl',
        'xxl',
        'xxxl',
    ];

    public function __construct(ImportHistoricalDues $command, int $fiscalYear)
    {
        $this->command = $command;
        $this->fiscalYear = $fiscalYear;
    }

    /**
     * Convert row from historical dues spreadsheet to User + DuesTransaction + Payment.
     */
    public function onRow(Row $objRow): void
    {
        $row = $objRow->toArray();

        $packages = self::guessDuesPackages($row, $this->fiscalYear);

        if (0 === count($packages)) {
            $this->command->newLine();
            $this->command->error('Failed to match package(s)');
            $this->command->table(array_keys($row), [$row]);

            $packagesResponse = $this->command->ask('Enter comma-separated list of package(s) or leave blank to skip');

            if (null === $packagesResponse) {
                return;
            }

            $packages = collect(explode(',', $packagesResponse))->map(
                static function (string $packageId): DuesPackage {
                    return DuesPackage::where('id', trim($packageId))->firstOrFail();
                }
            );
        }

        try {
            $user = self::guessUser($row);
        } catch (Throwable $e) {
            $this->command->newLine();
            $this->command->error($e->getMessage());
            $this->command->table(array_keys($row), [$row]);

            $gtid = $this->command->ask('Enter correct GTID for user or leave blank to skip');

            if (null === $gtid) {
                return;
            }

            $user = User::where('gtid', $gtid)->first();

            if (null === $user) {
                CreateOrUpdateUserFromBuzzAPI::dispatchNow('gtid', $gtid, self::USER_CREATION_REASON_STRING);
                $user = User::where('gtid', $gtid)->firstOrFail();
            }
        }

        foreach ($packages as $package) {
            $guessShirtSize = self::guessShirtSize($row);
            if (null !== $guessShirtSize) {
                $user->shirt_size = $guessShirtSize;
                $user->save();
            }

            $transaction = DuesTransaction::firstOrNew(
                [
                    'dues_package_id' => $package->id,
                    'user_id' => $user->id,
                ]
            );
            $transaction->swag_shirt_provided = self::guessShirtProvided($row);
            $transaction->swag_polo_provided = self::guessPoloProvided($row);
            $transaction->save();

            $date = null;

            if (array_key_exists('date', $row)) {
                $date = $row['date'];
            }

            self::guessPayment($transaction, $date);
        }
    }

    /**
     * Guesses the user.
     *
     * @param array<string,string|int|null> $row
     *
     * @phan-suppress PhanTypeMismatchArgumentNullableInternal
     */
    private static function guessUser(array $row): User
    {
        if (array_key_exists('gtid', $row) && null !== $row['gtid'] && $row['gtid'] > 900000000) {
            $user = User::where('gtid', $row['gtid'])->first();
            if (null !== $user) {
                return $user;
            }

            CreateOrUpdateUserFromBuzzAPI::dispatchNow('gtid', $row['gtid'], self::USER_CREATION_REASON_STRING);

            return User::where('gtid', $row['gtid'])->firstOrFail();
        }

        $name = trim($row['name'] ?? $row['members_name']);

        $split_name = explode(' ', $name);

        $first_name = $split_name[0];
        $last_name = $split_name[count($split_name) - 1];

        $user = User::where('first_name', $first_name)->where('last_name', $last_name)->first();

        if (null !== $user) {
            return $user;
        }

        $accountsResponse = BuzzAPI::select(
            'gtGTID',
            'mail',
            'sn',
            'givenName',
            'eduPersonPrimaryAffiliation',
            'gtPrimaryGTAccountUsername',
            'gtAccountEntitlement',
            'uid'
        )->from(Resources::GTED_ACCOUNTS)
        ->where(
            [
                'filter' => '(&(sn='.$last_name.')(givenName='.$first_name.'))',
            ]
        )
        ->get();

        if (! $accountsResponse->isSuccessful()) {
            throw new Exception(
                'GTED accounts search failed with message '.$accountsResponse->errorInfo()->message
            );
        }

        $numResults = count($accountsResponse->json->api_result_data);

        if (0 === $numResults) {
            throw new Exception('GTED accounts search was successful but gave no results for '.$name);
        }

        if (1 === $numResults) {
            $account = $accountsResponse->first();

            foreach (CreateOrUpdateUserFromBuzzAPI::EXPECTED_ATTRIBUTES as $attr) {
                if (! property_exists($account, $attr)) {
                    throw new Exception('GTED accounts search returned one result but missing some attributes');
                }
            }

            CreateOrUpdateUserFromBuzzAPI::dispatchNow('gtid', $account->gtGTID, self::USER_CREATION_REASON_STRING);

            return User::where('gtid', $account->gtGTID)->firstOrFail();
        }

        $possible_accounts = [];
        foreach ($accountsResponse->json->api_result_data as $account) {
            foreach (CreateOrUpdateUserFromBuzzAPI::EXPECTED_ATTRIBUTES as $attr) {
                if (! property_exists($account, $attr)) {
                    continue;
                }
            }
            if ($account->gtPrimaryGTAccountUsername !== $account->uid) {
                continue;
            }
            if (count($account->gtAccountEntitlement) < 6) {
                continue;
            }
            if (! Str::endsWith($account->mail, '@gatech.edu')) {
                continue;
            }
            $possible_accounts[] = $account;
        }

        if (1 === count($possible_accounts)) {
            CreateOrUpdateUserFromBuzzAPI::dispatchNow(
                'gtid',
                $possible_accounts[0]->gtGTID,
                self::USER_CREATION_REASON_STRING
            );

            return User::where('gtid', $possible_accounts[0]->gtGTID)->firstOrFail();
        }

        throw new Exception(
            'GTED accounts search found '.count($accountsResponse->json->api_result_data).' possible accounts, '.
            count($possible_accounts).' met heuristics for '.$name.' - '.json_encode(
                collect($possible_accounts)->map(static function (object $account): string {
                    return $account->gtPersonDirectoryId;
                })->toArray()
            )
        );
    }

    /**
     * Guesses the user's shirt size.
     *
     * @param array<string,string|int|null> $row
     *
     * @phan-suppress PhanTypeMismatchArgumentNullableInternal
     */
    private static function guessShirtSize(array $row): ?string
    {
        $size_columns = [
            'fall_s' => 's',
            'fall_m' => 'm',
            'fall_l' => 'l',
            'fall_xl' => 'xl',
            'fall_xxl' => 'xxl',
            'spring_s' => 's',
            'spring_m' => 'm',
            'spring_l' => 'l',
            'spring_xl' => 'xl',
            'spring_xxl' => 'xxl',
        ];

        $value_columns = [
            'tee_shirt_size',
            'shirt_size',
        ];

        foreach ($value_columns as $column) {
            if (
                array_key_exists($column, $row)
                && null !== $row[$column]
                && in_array(strtolower($row[$column]), self::VALID_SHIRT_SIZES, true)
            ) {
                return strtolower($row[$column]);
            }
        }

        foreach ($size_columns as $column => $size) {
            if (array_key_exists($column, $row) && 1 === $row[$column]) {
                return $size;
            }
        }

        return null;
    }

    /**
     * Guesses the dues package(s).
     *
     * @param array<string,string|int|null> $row
     *
     * @return array<\App\Models\DuesPackage>
     */
    private static function guessDuesPackages(array $row, int $fiscalYearEnding): array
    {
        $fiscalYear = FiscalYear::where('ending_year', $fiscalYearEnding)->firstOrFail();

        $startingYear = $fiscalYear->ending_year - 1;
        $endingYear = $fiscalYear->ending_year;

        $fall = 'Fall '.$startingYear;
        $fallSlug = Str::slug($fall, '_');
        $spring = 'Spring '.$endingYear;
        $springSlug = Str::slug($spring, '_');
        $fullYear = 'Full Year ('.$startingYear.'-'.$endingYear.')';

        if (array_key_exists('term', $row)) {
            return [DuesPackage::where('name', 'Full Year' === $row['term'] ? $fullYear : $row['term'])->firstOrFail()];
        }

        if (array_key_exists('both', $row)) {
            if (1 === $row['both']) {
                return [DuesPackage::where('name', $fullYear)->firstOrFail()];
            }
        }

        if (array_key_exists('full_yr', $row)) {
            if (1 === $row['full_yr']) {
                return [DuesPackage::where('name', $fullYear)->firstOrFail()];
            }
        }

        if (array_key_exists($fallSlug, $row) && array_key_exists($springSlug, $row)) {
            if (1 === $row[$fallSlug] && 1 === $row[$springSlug]) {
                return [DuesPackage::where('name', $fullYear)->firstOrFail()];
            }

            if (1 === $row[$fallSlug]) {
                return [DuesPackage::where('name', $fall)->firstOrFail()];
            }

            if (1 === $row[$springSlug]) {
                return [DuesPackage::where('name', $spring)->firstOrFail()];
            }

            if (null === $row[$fallSlug] && null === $row[$springSlug]) {
                return [];
            }

            if ($fall === $row[$fallSlug] && $spring === $row[$springSlug]) {
                return [
                    DuesPackage::where('name', $fall)->firstOrFail(),
                    DuesPackage::where('name', $spring)->firstOrFail(),
                ];
            }

            if ('Full Year' === $row[$fallSlug] && 'Full Year' === $row[$springSlug]) {
                return [DuesPackage::where('name', $fullYear)->firstOrFail()];
            }

            if ($fall === $row[$fallSlug] && null === $row[$springSlug]) {
                return [DuesPackage::where('name', $fall)->firstOrFail()];
            }

            if (null === $row[$fallSlug] && $spring === $row[$springSlug]) {
                return [DuesPackage::where('name', $spring)->firstOrFail()];
            }
        }

        return [];
    }

    /**
     * Guesses whether the shirt was provided.
     *
     * @param array<string,string|int|null> $row
     */
    private static function guessShirtProvided(array $row): ?Carbon
    {
        $candidateColumns = [
            'fall_received_shirt',
            'fall_recieved_shirt',
            'recieved_shirt',
            'received_shirt',
            'received_shirt_spring',
            'received_shirt_fall',
        ];

        foreach ($candidateColumns as $column) {
            if (array_key_exists($column, $row) && (1 === $row[$column] || 'Yes' === $row[$column])) {
                return Carbon::now();
            }
        }

        return null;
    }

    /**
     * Guesses whether the polo was provided.
     *
     * @param array<string,string|int|null> $row
     */
    private static function guessPoloProvided(array $row): ?Carbon
    {
        $candidateColumns = [
            'spring_received_shirt',
            'spring_recieved_shirt',
            'received_polo',
            'recieved_polo',
            'received_polo_fall',
            'received_polo_spring',
        ];

        foreach ($candidateColumns as $column) {
            if (array_key_exists($column, $row) && (1 === $row[$column] || 'Yes' === $row[$column])) {
                return Carbon::now();
            }
        }

        return null;
    }

    private static function guessPayment(DuesTransaction $transaction, ?string $date): void
    {
        $countExisting = Payment::where('payable_id', $transaction->id)->count();
        if (0 === $countExisting) {
            $payment = new Payment();
        } elseif (1 === $countExisting) {
            $payment = Payment::where('payable_id', $transaction->id)->firstOrFail();
        } else {
            throw new Exception('Found '.$countExisting.' existing payments for '.$transaction->id);
        }

        $payment->payable_id = $transaction->id;
        $payment->payable_type = $transaction->getMorphClass();
        $payment->amount = $transaction->package->cost;
        $payment->method = 'unknown';
        $payment->notes = 'Historical dues import';

        $squareCashTransaction = SquareCashTransaction::guessFromDuesTransaction($transaction);

        if (null !== $squareCashTransaction) {
            $payment->updateFromSquareCashTransaction($squareCashTransaction);

            return;
        }

        $squareTransaction = SquareTransaction::guessFromDuesTransaction($transaction);

        if (null !== $squareTransaction) {
            $payment->updateFromSquareTransaction($squareTransaction);

            return;
        }

        if (null !== $date) {
            $payment->created_at = Carbon::parse($date, config('app.timezone'))->startOfDay();
            $payment->updated_at = $payment->created_at;
        }

        $payment->save();
    }
}
