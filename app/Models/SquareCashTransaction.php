<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SquareCashTransaction
 *
 * @property int $id
 * @property string $transaction_id
 * @property \Illuminate\Support\Carbon $transaction_timestamp
 * @property float $amount
 * @property string|null $note
 * @property string $name_of_sender
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction newModelQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction newQuery()
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction query()
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereAmount($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereCreatedAt($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereId($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereNameOfSender($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereNote($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereTransactionId($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereTransactionTimestamp($value)
 * @method   static \Illuminate\Database\Eloquent\Builder|SquareCashTransaction whereUpdatedAt($value)
 * @mixin    \Eloquent
 */
class SquareCashTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_id',
        'transaction_timestamp',
        'amount',
        'note',
        'name_of_sender',
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
        // GTID in notes
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('note', 'like', '%'.$transaction->user->gtid.'%')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Username in notes
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('note', 'like', '%'.$transaction->user->uid.'%')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's name
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->first_name.' '.$transaction->user->last_name)
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's GT username
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->uid)
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's GT email
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->gt_email)
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's GT username@gatech.edu
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->uid.'@gatech.edu')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's GT username@gmail.com
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->uid.'@gmail.com')
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's verified Gmail address
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->gmail_address)
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        // Sender name matches user's personal email
        $query = self::whereDate('transaction_timestamp', '>=', $transaction->package->effective_start)
            ->whereDate('transaction_timestamp', '<=', $transaction->package->effective_end)
            ->where('amount', $transaction->package->cost)
            ->where('name_of_sender', $transaction->user->personal_email)
            ->get();

        if (1 === $query->count()) {
            return $query->first();
        }
        if ($query->count() > 1) {
            throw new Exception('Multiple Square Cash transactions matched for '.$transaction->id);
        }

        return null;
    }
}
