<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteAccount
{
    public function handle(User $user, Account $account): void
    {
        DB::transaction(function () use ($user, $account): void {
            $account->delete();

            if ($user->settings()->value('active_account_id') === $account->getKey()) {
                $user->settings()->update(['active_account_id' => null]);
            }
        });
    }
}
