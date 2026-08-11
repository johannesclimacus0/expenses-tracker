<?php

namespace App\Policies;

use App\Enums\AccountRole;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $this->isAccountMember($user, $transaction);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->owns($user, $transaction)
            || $this->isAccountOwner($user, $transaction);
    }

    private function owns(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->getKey();
    }

    private function isAccountMember(User $user, Transaction $transaction): bool
    {
        return $transaction->account_id !== null
            && $user->accountMemberships()
                ->where('account_id', $transaction->account_id)
                ->exists();
    }

    private function isAccountOwner(User $user, Transaction $transaction): bool
    {
        return $transaction->account_id !== null
            && $user->accountMemberships()
                ->where('account_id', $transaction->account_id)
                ->where('role', AccountRole::Owner->value)
                ->exists();
    }
}
