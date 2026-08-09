<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\DTOs\Goals\GoalContributionData;
use App\Enums\GoalContributionType;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class AddGoalContribution
{
    public function __construct(private SyncGoalStatus $syncStatus) {}

    public function handle(Goal $goal, GoalContributionData $data): GoalContribution
    {
        if ($data->type !== GoalContributionType::Deposit) {
            throw new InvalidArgumentException('Для пополнения ожидается тип deposit');
        }

        return DB::transaction(function () use ($goal, $data): GoalContribution {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->getKey());
            $contribution = $lockedGoal->contributions()->create([
                'type' => $data->type,
                'amount' => $data->amount,
                'contributed_at' => $data->contributedAt,
                'note' => $data->note,
            ]);

            $this->syncStatus->handle($lockedGoal);

            return $contribution;
        });
    }
}
