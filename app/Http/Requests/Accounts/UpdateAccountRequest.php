<?php

namespace App\Http\Requests\Accounts;

use App\Models\Account;

class UpdateAccountRequest extends StoreAccountRequest
{
    public function authorize(): bool
    {
        $account = $this->route('account');

        return $account instanceof Account
            && ($this->user()?->can('update', $account) ?? false);
    }
}
