<?php

namespace App\Policies;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Account $account): bool
    {
        return $this->isOwner($user, $account);
    }

    public function delete(User $user, Account $account): bool
    {
        return $this->isOwner($user, $account)
            && $user->accounts()->whereKeyNot($account->getKey())->exists();
    }

    public function manageMembers(User $user, Account $account): bool
    {
        return $this->isOwner($user, $account);
    }

    private function isOwner(User $user, Account $account): bool
    {
        return $account->members()
            ->where('user_id', $user->getKey())
            ->where('role', AccountRole::Owner->value)
            ->exists();
    }
}
