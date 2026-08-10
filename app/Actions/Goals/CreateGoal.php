<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\Actions\Accounts\ResolveCurrentAccount;
use App\DTOs\Goals\GoalData;
use App\Models\Goal;
use App\Models\User;

final readonly class CreateGoal
{
    public function __construct(private ResolveCurrentAccount $accounts) {}

    public function handle(User $user, GoalData $data): Goal
    {
        $account = $this->accounts->handle($user);

        return $user->goals()->create([
            'account_id' => $account->getKey(),
            'name' => $data->name,
            'target_amount' => $data->targetAmount,
            'deadline' => $data->deadline,
            'status' => $data->status,
        ]);
    }
}
