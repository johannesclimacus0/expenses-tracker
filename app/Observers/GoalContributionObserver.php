<?php

namespace App\Observers;

use App\Actions\Goals\SyncGoalStatus;
use App\Models\GoalContribution;

class GoalContributionObserver
{
    public function __construct(private SyncGoalStatus $syncGoalStatus) {}

    public function created(GoalContribution $goalContribution): void
    {
        $this->syncGoalStatus->handle($goalContribution->goal);
    }

    public function deleted(GoalContribution $goalContribution): void
    {
        $this->syncGoalStatus->handle($goalContribution->goal);
    }
}
