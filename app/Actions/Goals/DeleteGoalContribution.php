<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\Enums\GoalContributionType;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Services\Goals\GoalProgressService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class DeleteGoalContribution
{
    public function __construct(private GoalProgressService $progressService) {}

    public function handle(Goal $goal, GoalContribution $contribution): void
    {
        DB::transaction(function () use ($goal, $contribution): void {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->getKey());
            $lockedContribution = GoalContribution::query()
                ->whereBelongsTo($lockedGoal)
                ->lockForUpdate()
                ->findOrFail($contribution->getKey());

            if ($lockedContribution->type === GoalContributionType::Deposit) {
                $progress = $this->progressService->for($lockedGoal);
                $afterDeletion = bcsub($progress->currentAmount, $lockedContribution->amount, 2);

                if (bccomp($afterDeletion, '0.00', 2) < 0) {
                    throw ValidationException::withMessages([
                        'contribution' => 'Сначала удалите связанные снятия.',
                    ]);
                }
            }

            $lockedContribution->delete();
        });
    }
}
