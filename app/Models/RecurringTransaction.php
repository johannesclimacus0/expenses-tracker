<?php

namespace App\Models;

use App\Enums\RecurringPeriod;
use App\Enums\TransactionType;
use App\Traits\HasUuidRouteKey;
use Carbon\CarbonImmutable;
use Database\Factories\RecurringTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $user_id
 * @property int|null $account_id
 * @property int|null $category_id
 * @property TransactionType $type
 * @property numeric $amount
 * @property string|null $description
 * @property RecurringPeriod $period
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $next_run_at
 * @property CarbonImmutable|null $last_run_at
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Account|null $account
 * @property-read Category|null $category
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read User $user
 *
 * @method static \Database\Factories\RecurringTransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereLastRunAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereNextRunAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecurringTransaction whereUuid($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['account_id', 'category_id', 'type', 'amount', 'description', 'period', 'starts_at', 'next_run_at', 'last_run_at', 'is_active'])]
class RecurringTransaction extends Model
{
    /** @use HasFactory<RecurringTransactionFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'type' => TransactionType::class,
        'period' => RecurringPeriod::class,
        'amount' => 'decimal:2',
        'starts_at' => 'immutable_datetime',
        'next_run_at' => 'immutable_datetime',
        'last_run_at' => 'immutable_datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
