<?php

namespace Database\Factories\Concerns;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

trait CreatesAccountMembership
{
    private function defaultAccountId(array $attributes): Factory|int
    {
        $userId = $attributes['user_id'] ?? null;

        if (is_numeric($userId)) {
            $accountId = User::query()
                ->find((int) $userId)
                ?->accounts()
                ->oldest('accounts.id')
                ->value('accounts.id');

            if ($accountId !== null) {
                return (int) $accountId;
            }
        }

        return Account::factory();
    }

    private function createAccountMembership(int $userId, ?int $accountId): void
    {
        if ($accountId === null) {
            return;
        }

        $exists = AccountMember::query()
            ->where('account_id', $accountId)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return;
        }

        AccountMember::factory()->create([
            'account_id' => $accountId,
            'user_id' => $userId,
            'role' => AccountRole::Member,
        ]);
    }
}
