<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Models\Account;
use App\Models\User;

final readonly class ResolveCurrentAccount
{
    public function __construct(private CreatePersonalAccount $createPersonalAccount) {}

    public function handle(User $user): Account
    {
        $activeAccountId = $user->settings()->value('active_account_id');

        if ($activeAccountId !== null) {
            $activeAccount = $user->accounts()
                ->whereKey($activeAccountId)
                ->first();

            if ($activeAccount !== null) {
                return $activeAccount;
            }
        }

        $account = $user->accounts()
            ->oldest('accounts.id')
            ->first();

        return $account ?? $this->createPersonalAccount->handle($user);
    }
}
