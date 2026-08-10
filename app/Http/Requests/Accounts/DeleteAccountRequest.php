<?php

namespace App\Http\Requests\Accounts;

use App\Models\Account;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->route('account');

        return $account instanceof Account
            && ($this->user()?->can('delete', $account) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
