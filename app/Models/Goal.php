<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GoalStatus;
use App\Traits\HasUuidRouteKey;
use Carbon\CarbonImmutable;
use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property int|null $account_id
 * @property string $name
 * @property numeric $target_amount
 * @property CarbonImmutable|null $deadline
 * @property GoalStatus $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Account|null $account
 * @property-read Collection<int, GoalContribution> $contributions
 * @property-read int|null $contributions_count
 * @property-read User $user
 *
 * @method static \Database\Factories\GoalFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Goal whereUuid($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['account_id', 'name', 'target_amount', 'deadline', 'status'])]
final class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'target_amount' => 'decimal:2',
        'deadline' => 'immutable_date',
        'status' => GoalStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
