<?php

declare(strict_types=1);

namespace App\Http\Controllers\Goals;

use App\Actions\Goals\AddGoalContribution;
use App\Actions\Goals\DeleteGoalContribution;
use App\Actions\Goals\WithdrawFromGoal;
use App\DTOs\Goals\GoalContributionData;
use App\Enums\GoalContributionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\DeleteGoalContributionRequest;
use App\Http\Requests\Goals\StoreGoalContributionRequest;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Http\RedirectResponse;

final class GoalContributionController extends Controller
{
    public function store(
        StoreGoalContributionRequest $request,
        Goal $goal,
        AddGoalContribution $addContribution,
        WithdrawFromGoal $withdrawFromGoal,
    ): RedirectResponse {
        $data = GoalContributionData::fromArray($request->validated());

        match ($data->type) {
            GoalContributionType::Deposit => $addContribution->handle($goal, $data),
            GoalContributionType::Withdrawal => $withdrawFromGoal->handle($goal, $data),
        };

        return to_route('goals.show', $goal)->with('status', 'Прогресс обновлён');
    }

    public function destroy(
        DeleteGoalContributionRequest $request,
        Goal $goal,
        GoalContribution $contribution,
        DeleteGoalContribution $deleteContribution,
    ): RedirectResponse {
        $deleteContribution->handle($goal, $contribution);

        return to_route('goals.show', $goal)->with('status', 'Операция удалена');
    }
}
