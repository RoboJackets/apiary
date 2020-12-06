<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Nova\Actions\Actionable;

/**
 * Represents a payment made from a member to RoboJackets against a Payable.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method \Illuminate\Database\Eloquent\Builder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCheckoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereClientTxnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePayableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProcessingFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereServerTxnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUniqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Payment onlyTrashed()
 * @method static \Illuminate\Database\Query\Builder|Payment withoutTrashed()
 * @method static \Illuminate\Database\Query\Builder|Payment withTrashed()
 *
 * @mixin \Barryvdh\LaravelIdeHelper\Eloquent
 *
 * @property \Carbon\Carbon $created_at when the model was created
 * @property \Carbon\Carbon $updated_at when the model was updated
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property float $amount The amount of this Payment
 * @property float $processing_fee
 * @property int $id The database ID of this Payment
 * @property int $payable_id the ID of the payable model
 * @property int $recorded_by The ID of the user that recorded this Payment
 * @property string $checkout_id A unique identifier for this Payment generated by Square, used to redirect users to
 * @property string $method The method of payment (see $methods)
 * @property string $notes Free-form notes regarding this Payment
 * @property string $payable_type the class of the payable model
 * @property string $unique_id A unique identifier for this Payment generated by Apiary, used for Square
 * @property string|null $client_txn_id
 * @property string|null $server_txn_id
 *
 * @property-read \App\DuesTransaction $payable
 * @property-read \App\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|array<\Laravel\Nova\Actions\ActionEvent> $actions
 * @property-read int|null $actions_count
 * @property-read string $method_presentation
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
}
