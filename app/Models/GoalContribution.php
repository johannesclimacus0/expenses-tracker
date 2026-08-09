<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GoalContributionType;
use App\Traits\HasUuidRouteKey;
use Carbon\CarbonImmutable;
use Database\Factories\GoalContributionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $uuid
 * @property int $goal_id
 * @property GoalContributionType $type
 * @property numeric $amount
 * @property CarbonImmutable $contributed_at
 * @property string|null $note
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Goal $goal
 *
 * @method static \Database\Factories\GoalContributionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereContributedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereGoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GoalContribution whereUuid($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['type', 'amount', 'contributed_at', 'note'])]
final class GoalContribution extends Model
{
    /** @use HasFactory<GoalContributionFactory> */
    use HasFactory, HasUuidRouteKey;

    protected $casts = [
        'type' => GoalContributionType::class,
        'amount' => 'decimal:2',
        'contributed_at' => 'immutable_datetime',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
