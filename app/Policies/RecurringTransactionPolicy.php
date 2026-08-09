<?php

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

class RecurringTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $this->owns($user, $recurringTransaction);
    }

    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $this->owns($user, $recurringTransaction);
    }

    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $this->owns($user, $recurringTransaction);
    }

    private function owns(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $recurringTransaction->user_id === $user->getKey();
    }
}
