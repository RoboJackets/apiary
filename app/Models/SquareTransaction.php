<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SquareTransaction.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $transaction_timestamp
 * @property float $amount
 * @property string $source
 * @property string $entry_method
 * @property float $processing_fee
 * @property string $transaction_id
 * @property string $payment_id
 * @property string $card_brand
 * @property string $last_4
 * @property string|null $device_name
 * @property string|null $staff_name
 * @property string $description
 * @property string|null $customer_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction newModelQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction newQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction query()
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereAmount($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereCardBrand($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereCreatedAt($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereCustomerName($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereDescription($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereDeviceName($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereEntryMethod($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereId($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereLast4($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction wherePaymentId($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereProcessingFee($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereSource($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereStaffName($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereTransactionId($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereTransactionTimestamp($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareTransaction whereUpdatedAt($value)
 * @mixin    \Barryvdh\LaravelIdeHelper\Eloquent
 */
class SquareTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_timestamp',
        'amount',
        'source',
        'entry_method',
        'processing_fee',
        'transaction_id',
        'payment_id',
        'card_brand',
        'last_4',
        'device_name',
        'staff_name',
        'description',
        'customer_name',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'transaction_timestamp' => 'datetime',
    ];

    public static function guessFromDuesTransaction(DuesTransaction $transaction): ?self
    {
        // Customer name field matches user's name
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', '>=', $transaction->package->cost)
            ->where('amount', '<=', $transaction->package->cost + 5)
            ->where('customer_name', $transaction->user->first_name.' '.$transaction->user->last_name)
            ->where('description', 'not like', '%retreat%')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square transactions matched for '.$transaction->id);
        }

        // Customer name field matches user's GT email
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', '>=', $transaction->package->cost)
            ->where('amount', '<=', $transaction->package->cost + 5)
            ->where('customer_name', $transaction->user->gt_email)
            ->where('description', 'not like', '%retreat%')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square transactions matched for '.$transaction->id);
        }

        // Customer name field matches user's GT username@gatech.edu
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', '>=', $transaction->package->cost)
            ->where('amount', '<=', $transaction->package->cost + 5)
            ->where('customer_name', $transaction->user->uid.'@gatech.edu')
            ->where('description', 'not like', '%retreat%')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square transactions matched for '.$transaction->id);
        }

        // Customer name field matches user's GT username@gmail.com
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', '>=', $transaction->package->cost)
            ->where('amount', '<=', $transaction->package->cost + 5)
            ->where('customer_name', $transaction->user->uid.'@gmail.com')
            ->where('description', 'not like', '%retreat%')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square transactions matched for '.$transaction->id);
        }

        // Customer name field matches user's verified Gmail address
        if (null !== $transaction->user->gmail_address) {
            $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
                ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
                ->where('amount', '>=', $transaction->package->cost)
                ->where('amount', '<=', $transaction->package->cost + 5)
                ->where('customer_name', $transaction->user->gmail_address)
                ->where('description', 'not like', '%retreat%')
                ->get();

            if (1 === $query->count()) {
                return $query->first();
            }
            if ($query->count() > 1) {
                throw new Exception('Multiple Square transactions matched for '.$transaction->id);
            }
        }

        // Customer name field matches user's personal email
        if (null !== $transaction->user->personal_email) {
            $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
                ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
                ->where('amount', '>=', $transaction->package->cost)
                ->where('amount', '<=', $transaction->package->cost + 5)
                ->where('customer_name', $transaction->user->personal_email)
                ->where('description', 'not like', '%retreat%')
                ->get();

            if (1 === $query->count()) {
                return $query->first();
            }
            if ($query->count() > 1) {
                throw new Exception('Multiple Square transactions matched for '.$transaction->id);
            }
        }

        return null;
    }

    public function guessRecordedBy(): ?int
    {
        $device_name_map = [
            'Matt\'s iPhone' => ['Matthew', 'Barulic'],
            'Ryan Strat' => ['Ryan', 'Strat'],
        ];

        if (null === $this->device_name && null === $this->staff_name) {
            return null;
        }

        if (null !== $this->staff_name && '' !== $this->staff_name) {
            $name = explode(' ', $this->staff_name);

            return User::where('first_name', $name[0])->where('last_name', $name[1])->firstOrFail()->id;
        }

        $name = $device_name_map[$this->device_name];

        return User::where('first_name', $name[0])->where('last_name', $name[1])->firstOrFail()->id;
    }
}
