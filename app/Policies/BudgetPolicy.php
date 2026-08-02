<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Budget $budget): bool
    {
        return $this->owns($user, $budget);
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $this->owns($user, $budget);
    }

    private function owns(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->getKey();
    }
}
