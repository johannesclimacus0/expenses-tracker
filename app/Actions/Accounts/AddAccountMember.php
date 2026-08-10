<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;

final class AddAccountMember
{
    public function handle(Account $account, User $user): AccountMember
    {
        return $account->members()->create([
            'user_id' => $user->getKey(),
            'role' => AccountRole::Member,
        ]);
    }
}
