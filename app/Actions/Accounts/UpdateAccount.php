<?php

declare(strict_types=1);

namespace App\Actions\Accounts;

use App\DTOs\Accounts\AccountData;
use App\Models\Account;

final class UpdateAccount
{
    public function handle(Account $account, AccountData $data): Account
    {
        $account->update(['name' => $data->name, 'currency' => $data->currency]);

        return $account->refresh();
    }
}
