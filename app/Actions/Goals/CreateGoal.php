<?php

declare(strict_types=1);

namespace App\Actions\Goals;

use App\DTOs\Goals\GoalData;
use App\Models\Goal;
use App\Models\User;

final class CreateGoal
{
    public function handle(User $user, GoalData $data): Goal
    {
        return $user->goals()->create([
            'name' => $data->name,
            'target_amount' => $data->targetAmount,
            'deadline' => $data->deadline,
            'status' => $data->status,
        ]);
    }
}
