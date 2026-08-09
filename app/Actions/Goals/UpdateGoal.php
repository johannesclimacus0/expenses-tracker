<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\DTOs\Goals\GoalData;
use App\Models\Goal;

final readonly class UpdateGoal
{
    public function __construct(private SyncGoalStatus $syncStatus) {}

    public function handle(Goal $goal, GoalData $data): Goal
    {
        $goal->update([
            'name' => $data->name,
            'target_amount' => $data->targetAmount,
            'deadline' => $data->deadline,
            'status' => $data->status,
        ]);

        $this->syncStatus->handle($goal);

        return $goal->refresh();
    }
}
