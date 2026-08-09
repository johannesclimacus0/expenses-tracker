<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\DTOs\Goals\GoalContributionData;
use App\Enums\GoalContributionType;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Services\Goals\GoalProgressService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final readonly class WithdrawFromGoal
{
    public function __construct(
        private GoalProgressService $progressService,
        private SyncGoalStatus $syncStatus,
    ) {}

    public function handle(Goal $goal, GoalContributionData $data): GoalContribution
    {
        if ($data->type !== GoalContributionType::Withdrawal) {
            throw new InvalidArgumentException('Для снятия ожидается тип withdrawal');
        }

        return DB::transaction(function () use ($goal, $data): GoalContribution {
            $lockedGoal = Goal::query()->lockForUpdate()->findOrFail($goal->getKey());
            $progress = $this->progressService->for($lockedGoal);

            if (bccomp($data->amount, $progress->currentAmount, 2) > 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Нельзя снять больше, чем накоплено',
                ]);
            }

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
