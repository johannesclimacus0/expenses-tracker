<?php

namespace App\Policies;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;

final class GoalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Goal $goal): bool
    {
        return $this->owns($user, $goal);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Goal $goal): bool
    {
        return $this->owns($user, $goal);
    }

    public function delete(User $user, Goal $goal): bool
    {
        return $this->owns($user, $goal);
    }

    public function contribute(User $user, Goal $goal): bool
    {
        return $this->owns($user, $goal)
            && !in_array($goal->status, [GoalStatus::Paused, GoalStatus::Cancelled], true);
    }

    private function owns(User $user, Goal $goal): bool
    {
        return $goal->user_id === $user->getKey();
    }
}
