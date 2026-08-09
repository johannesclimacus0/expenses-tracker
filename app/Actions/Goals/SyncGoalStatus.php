<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Services\Goals\GoalProgressService;

final readonly class SyncGoalStatus
{
    public function __construct(private GoalProgressService $progressService) {}

    public function handle(Goal $goal): void
    {
        $progress = $this->progressService->for($goal);

        if ($progress->isCompleted && $goal->status !== GoalStatus::Completed) {
            $goal->update(['status' => GoalStatus::Completed]);

            return;
        }

        if (!$progress->isCompleted && $goal->status === GoalStatus::Completed) {
            $goal->update(['status' => GoalStatus::Active]);
        }
    }
}
