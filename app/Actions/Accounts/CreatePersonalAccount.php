<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Enums\AccountRole;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreatePersonalAccount
{
    public function handle(User $user): Account
    {
        $existingAccount = $user->accounts()
            ->oldest('accounts.id')
            ->first();

        if ($existingAccount !== null) {
            return $existingAccount;
        }

        return DB::transaction(function () use ($user): Account {
            $account = Account::query()->create([
                'name' => 'Личный счет',
                'currency' => $user->settings()->first()?->currency ?? Currency::Rub,
            ]);

            AccountMember::query()->create([
                'account_id' => $account->getKey(),
                'user_id' => $user->getKey(),
                'role' => AccountRole::Owner,
            ]);

            return $account;
        });
    }
}
