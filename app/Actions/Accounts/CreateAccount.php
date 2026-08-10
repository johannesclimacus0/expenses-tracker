<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\DTOs\Accounts\AccountData;
use App\Enums\AccountRole;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateAccount
{
    public function handle(User $user, AccountData $data): Account
    {
        return DB::transaction(function () use ($user, $data): Account {
            $account = Account::query()->create([
                'name' => $data->name,
                'currency' => $data->currency,
            ]);

            $account->members()->create([
                'user_id' => $user->getKey(),
                'role' => AccountRole::Owner,
            ]);

            return $account;
        });
    }
}
