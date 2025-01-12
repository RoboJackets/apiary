<?php

declare(strict_types=1);

// phpcs:disable SlevomatCodingStandard.PHP.UselessParentheses.UselessParentheses

namespace App\Models;
use App\Events\PaymentDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Actionable;
use Square\SquareClient;

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
 * @property bool $receipt_sent
 * @property string|null $url
 * @property-read int|null $actions_count
 * @property-read string $method_presentation
 * @property-read \App\Models\DuesTransaction|\App\Models\TravelAssignment $payable
 * @property-read \App\Models\User|null $user
 *
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
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereReceiptSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereReceiptUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereServerTxnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereSquareCashTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatementDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|Payment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Payment withoutTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property-read \App\Models\DuesTransaction|null $duesTransaction
 * @property-read \App\Models\User|null $recordedBy
 * @property-read \App\Models\TravelAssignment|null $travelAssignment
 *
 * @phan-suppress PhanUnreferencedPublicClassConstant
 */
class Payment extends Model
{
    use Actionable;
    use HasFactory;
    use SoftDeletes;

    private const PER_TRANSACTION_FEE = 30;  // cents

    private const PERCENTAGE_FEE = 2.9;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['method_presentation'];

    protected $dispatchesEvents = [
        'deleting' => PaymentDeleted::class,
    ];

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'receipt_sent' => 'boolean',
        ];
    }

    public const RELATIONSHIP_PERMISSIONS = [
        'user' => 'read-users',
        'payable' => 'read-dues-transactions',
        'duesTransaction' => 'read-dues-transactions',
        'travelAssignment' => 'read-travel-assignments',
    ];

    /**
     * Get all the owning payable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\App\Models\DuesTransaction|\App\Models\TravelAssignment,\App\Models\Payment>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the owning payable model as a DuesTransaction.
     *
     * @return BelongsTo<DuesTransaction, Payment>
     */
    public function duesTransaction(): BelongsTo
    {
        return $this->belongsTo(DuesTransaction::class, 'payable_id', 'id');
    }

    /**
     * Get the owning payable model as a TravelAssignment.
     *
     * @return BelongsTo<TravelAssignment, Payment>
     */
    public function travelAssignment(): BelongsTo
    {
        return $this->belongsTo(TravelAssignment::class, 'payable_id', 'id');
    }

    /**
     * Get the User associated with the Payment model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Payment>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the User associated with the Payment model. This is only used by Nova to show a list of Payments
     * associated with a payable resource (DuesTransaction or Travel Assignment).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Payment>
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
     * Generates a cryptographically safe unique string. Currently used for idempotency keys for the Square API.
     */
    public static function generateUniqueId(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    /**
     * Calculates the surcharge to add to a transaction for Square so that RoboJackets receives the input amount.
     *
     * Fees are listed at https://squareup.com/us/en/payments/our-fees.
     *
     * @param  int  $amount  desired net amount, in cents
     * @return int surcharge amount, in cents
     */
    public static function calculateSurcharge(int $amount): int
    {
        return (int) round(
            (($amount + self::PER_TRANSACTION_FEE) / ((100 - self::PERCENTAGE_FEE) / 100)) - $amount,
            0,
            PHP_ROUND_HALF_UP
        );
    }

    /**
     * Calculates the processing fee for a given amount.
     *
     * Fees are listed at https://squareup.com/us/en/payments/our-fees.
     *
     * @param  int  $amount  charge amount, in cents
     * @return int processing fee, in cents
     */
    public static function calculateProcessingFee(int $amount): int
    {
        return (int) round(self::PER_TRANSACTION_FEE + ((self::PERCENTAGE_FEE / 100) * $amount));
    }

    public function getSquareOrderState(): ?string
    {
        if ($this->order_id === null) {
            return null;
        }

        return Cache::remember(
            'square_payment_status_'.$this->order_id,
            10,
            fn (): string => (new SquareClient(
                [
                    'accessToken' => config('square.access_token'),
                    'environment' => config('square.environment'),
                ]
            ))->getOrdersApi()
                ->retrieveOrder($this->order_id)
                ->getResult()
                ->getOrder()
                ->getState()
        );
    }
}
