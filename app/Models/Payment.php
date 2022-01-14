<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a payment made from a member to RoboJackets against a Payable.
 *
 * @property int $id
 * @property int $payable_id
 * @property string $payable_type
 * @property string $amount
 * @property string|null $processing_fee
 * @property string $method
 * @property int|null $recorded_by
 * @property string|null $checkout_id
 * @property string|null $client_txn_id
 * @property string|null $server_txn_id
 * @property string|null $unique_id
 * @property string|null $order_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $card_brand
 * @property string|null $card_type
 * @property string|null $last_4
 * @property string|null $prepaid_type
 * @property string|null $entry_method
 * @property string|null $statement_description
 * @property string|null $receipt_number
 * @property string|null $receipt_url
 * @property string|null $square_cash_transaction_id
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read string $method_presentation
 * @property-read Model|\Barryvdh\LaravelIdeHelper\Eloquent $payable
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\PaymentFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Query\Builder|Payment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCheckoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereClientTxnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereEntryMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereLast4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePrepaidType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereReceiptNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereReceiptUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereServerTxnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereSquareCashTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatementDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Payment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Payment withoutTrashed()
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 */
class Payment extends Model
{
    use Actionable;
    use HasFactory;
    use SoftDeletes;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['method_presentation'];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * All payment methods. Note that you probably want to also create a permission in the database for any
     * new methods added here.
     *
     * @var array<string,string>
     *
     * @phan-suppress PhanReadOnlyPublicProperty
     */
    public static $methods = [
        'cash' => 'Cash',
        'squarecash' => 'Square Cash',
        'check' => 'Check',
        'swipe' => 'Swiped Card',
        'square' => 'Square Checkout',
        'waiver' => 'Waiver',
        'unknown' => 'Unknown',
    ];

    /**
     * Get all of the owning payable models.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the User associated with the Payment model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the presentation-ready format of a Payment Method.
     */
    public function getMethodPresentationAttribute(): string
    {
        return array_key_exists($this->method, self::$methods) ? self::$methods[$this->method] : '';
    }

    /**
     * Map of relationships to permissions for dynamic inclusion.
     *
     * @return array<string,string>
     */
    public function getRelationshipPermissionMap(): array
    {
        return [
            'user' => 'users',
            'payable' => 'dues-transactions',
        ];
    }

    public static function generateUniqueId(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }
}
